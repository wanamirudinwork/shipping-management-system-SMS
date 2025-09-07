<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */


class ParserModifyLayoutView extends ModuleBuilderParser
{
    public $maxColumns; // number of columns in this layout
    public $usingWorkingFile = false; // if a working file exists (used by view.edit.php among others to determine the title for the layout edit panel)
    // @codingStandardsIgnoreStart PSR2.Classes.PropertyDeclaration.Underscore
    public $_baseDirectory;
    /**
     * @var string|class-string<\EditView>|mixed|mixed[]
     */
    public $_sourceView;
    public $language_module; // set to module name for studio, passed to the smarty template and used by sugar_translate
    public $_sourceFile; // private - the source of the layout defn
    public $_customFile; // private
    public $_workingFile; // private
    public $_originalFile; //private
    public $_moduleVariable; // private - if set, contains the name of the variable containing the module name in the $viewdef file
    public $_module; // private
    public $_view; // private
    public $_viewdefs; // private
    public $_fieldDefs; // private
    // @codingStandardsIgnoreEnd PSR2.Classes.PropertyDeclaration.Underscore


    /**
     * {@inheritDoc}
     *
     * @param string $module
     * @param string $view
     */
    public function init($module, $view = '')
    {
        $submittedLayout = null;
        if (empty($view)) {
            throw new \BadMethodCallException('Missing required argument $view');
        }
        $this->_view = ucfirst($view);
        $this->_module = $module;
        $this->language_module = $module;

        $this->_baseDirectory = "modules/{$module}/metadata/";
        $file = $this->_baseDirectory . strtolower($view) . 'defs.php';
        $this->_customFile = 'custom/' . $file;
        $this->_workingFile = 'custom/working/' . $file;

        $this->_sourceView = $this->_view;
        $this->_originalFile = $file;
        $this->_sourceFile = $file;
        if (is_file($this->_workingFile)) {
            $this->_sourceFile = $this->_workingFile;
            $this->usingWorkingFile = true;
        } elseif (is_file($this->_customFile)) {
            $this->_sourceFile = $this->_customFile;
        } elseif (!is_file($this->_sourceFile)) {
            // if we don't have ANY defined metadata then improvise as best we can
            if (strtolower($this->_view) == 'quickcreate') {
                // special handling for quickcreates - base the quickcreate on the editview if no quickcreatedef exists
                $this->_sourceFile = $this->_baseDirectory . 'editviewdefs.php';
                if (is_file('custom/' . $this->_sourceFile)) {
                    $this->_sourceFile = 'custom/' . $this->_sourceFile;
                }
                $this->_sourceView = 'EditView';
            } else {
                $this->_fatalError('parser.modifylayout.php->init(): no metadata for ' . $this->_module . ' ' . $this->_view);
            }
        }

        // get the fieldDefs from the bean
        $bean = BeanFactory::newBean($module);
        $this->_fieldDefs = &$bean->field_defs;

        $this->loadModule($this->_module, $this->_sourceView);
        $this->_viewdefs ['panels'] = $this->_parseData($this->_viewdefs['panels']); // put into a canonical format
        $this->maxColumns = $this->_viewdefs ['templateMeta'] ['maxColumns'];

        if ($submittedLayout) {
            // replace the definitions with the new submitted layout
            $this->_loadLayoutFromRequest();
        } else {
            $this->_padFields(); // destined for a View, so we want to add in (empty) fields
        }
        //      $GLOBALS['log']->debug($this->_viewdefs['panels']);
    }

    public function getAvailableFields()
    {
        // Available fields are those that are in the Model and the original layout definition, but not already shown in the View
        // So, because the formats of the two are different we brute force loop through View and unset the fields we find in a copy of Model
        $availableFields = $this->_getModelFields();
        $GLOBALS['log']->debug(get_class($this) . '->getAvailableFields(): _getModelFields returns: ' . implode(',', array_keys($availableFields)));
        if (!empty($this->_viewdefs)) {
            foreach ($this->_viewdefs ['panels'] as $panel) {
                foreach ($panel as $row) {
                    foreach ($row as $fieldArray) { // fieldArray is an array('name'=>name,'label'=>label)
                        if (isset($fieldArray ['name'])) {
                            unset($availableFields [$fieldArray ['name']]);
                            $GLOBALS['log']->debug(get_class($this) . '->getAvailableFields(): removing ' . $fieldArray ['name']);
                        }
                    }
                }
            }
        }
        return $availableFields;
    }

    public function getLayout()
    {
        return $this->_viewdefs ['panels'];
    }

    public function writeWorkingFile()
    {
        $this->_writeToFile($this->_workingFile, $this->_view, $this->_module, $this->_viewdefs, $this->_variables);
    }

    public function handleSave()
    {
        $this->_writeToFile($this->_customFile, $this->_view, $this->_module, $this->_viewdefs, $this->_variables);
        // now clear the cache so that the results are immediately visible
        include_once 'include/TemplateHandler/TemplateHandler.php';
        if (strtolower($this->_view) == 'quickcreate') {
            TemplateHandler::clearCache($this->_module, "form_SubPanelQuickCreate_{$this->_module}.tpl");
            TemplateHandler::clearCache($this->_module, "form_DCQuickCreate_{$this->_module}.tpl");
        } else {
            TemplateHandler::clearCache($this->_module, "{$this->_view}.tpl");
        }
    }

    /**
     * Loads up the view defs into this parser
     *
     * @param string $module
     * @param string $view
     * @return void
     */
    public function loadModule($module, $view)
    {
        $this->_viewdefs = [];
        //$viewdefs = null;

        // Load the defs from the source file
        $loaded = $this->_loadFromFile($view, $this->_sourceFile, $module);
        $this->_viewdefs = $loaded['viewdefs'][$module][$view];
        $this->_variables = $loaded['variables'];
    }

    /**
     * Load the canonical panel layout from the submitted form
     *
     */
    // @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore
    public function _loadLayoutFromRequest()
    {

        $i = 1;
        // set up the map of panel# (as provided in the _REQUEST) to panel ID (as used in $this->_viewdefs['panels'])
        foreach ($this->_viewdefs ['panels'] as $panelID => $panel) {
            $panelMap [$i++] = $panelID;
        }
        // replace any old values with new panel labels from the request
        foreach ($_REQUEST as $key => $value) {
            $components = explode('-', $key);
            if ($components [0] == 'panel') {
                $panelMap [$components ['1']] = $value;
            }
        }

        $olddefs = $this->_viewdefs ['panels'];
        $origFieldDefs = $this->_getOrigFieldViewDefs();
        //      $GLOBALS['log']->debug('origFieldDefs');
        //      $GLOBALS['log']->debug($origFieldDefs);
        $this->_viewdefs ['panels'] = null; // because the new field properties should replace the old fields, not be merged

        if ($this->maxColumns < 1) {
            $this->_fatalError('EditDetailViewParser:invalid maxColumns=' . $this->maxColumns);
        }

        foreach ($_REQUEST as $slot => $value) {
            $slotComponents = explode('-', $slot); // [0] = 'slot', [1] = panel #, [2] = slot #, [3] = property name
            if ($slotComponents [0] == 'slot') {
                $slotNumber = $slotComponents ['2'];
                $panelID = $panelMap [$slotComponents ['1']];
                $rowID = floor($slotNumber / $this->maxColumns); // This might be able to go away
                $colID = $slotNumber - ($rowID * $this->maxColumns); // This is going to need to go away
                //If the original editview defined this field, copy that over.
                if ($slotComponents ['3'] == 'name' && isset($origFieldDefs [$value]) && is_array($origFieldDefs [$value])) {
                    $this->_viewdefs ['panels'] [$panelID] [$rowID] [$colID] = $origFieldDefs [$value];
                } else {
                    $property = $slotComponents ['3'];
                    if ($value == '(filler)') {
                        $this->_viewdefs ['panels'] [$panelID] [$rowID] [$colID] = null;
                    } else {
                        $this->_viewdefs ['panels'] [$panelID] [$rowID] [$colID] [$property] = $value;
                    }
                }
            }
        }

        // Now handle the (empty) fields - first non-(empty) field goes in at column 0; all other (empty)'s removed
        // Do this AFTER reading in all the $_REQUEST parameters as can't guarantee the order of those, and we need to operate on complete rows
        foreach ($this->_viewdefs ['panels'] as $panelID => $panel) {
            // remove all (empty)s
            foreach ($panel as $rowID => $row) {
                $startOfRow = true;
                $offset = 0;
                foreach ($row as $colID => $col) {
                    if ($col ['name'] == '(empty)') {
                        // if a leading (empty) then remove (by noting that remaining fields need to be shuffled along)
                        if ($startOfRow) {
                            $offset++;
                        }
                        unset($row [$colID]);
                    } else {
                        $startOfRow = false;
                    }
                }
                // reindex to remove leading (empty)s
                $newRow = [];
                foreach ($row as $colID => $col) {
                    $newRow [$colID - $offset] = $col;
                }
                $this->_viewdefs ['panels'] [$panelID] [$rowID] = $newRow;
            }
        }
    }

    // @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore
    public function _padFields()
    {
        if (!empty($this->_viewdefs)) {
            foreach ($this->_viewdefs ['panels'] as $panelID => $panel) {
                $column = 0;
                foreach ($panel as $rowID => $row) {
                    // pad between fields on a row
                    foreach ($row as $colID => $col) {
                        for ($i = $column + 1; $i < $colID; $i++) {
                            $row [$i] = ['name' => '(empty)', 'label' => '(empty)'];
                        }
                        $column = $colID;
                    }
                    // now pad out to the end of the row
                    if (($column + 1) < $this->maxColumns) { // last column is maxColumns-1
                        for ($i = $column + 1; $i < $this->maxColumns; $i++) {
                            $row [$i] = ['name' => '(empty)', 'label' => '(empty)'];
                        }
                    }
                    ksort($row);
                    $this->_viewdefs ['panels'] [$panelID] [$rowID] = $row;
                }
            }
        }
    }


    // add a new field to the end of a panel
    // don't write out (caller should call handleSave() when ready)
    // @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore
    public function _addField($properties, $panelID = false)
    {

        // if a panelID was not passed, use the first available panel in the list
        if (!$panelID) {
            $panels = array_keys($this->_viewdefs['panels']);
            $panelID = $panels[0];
        }

        if (isset($this->_viewdefs ['panels'] [$panelID])) {
            // need to clean up the viewdefs before writing them -- Smarty will fail if any fillers/empties are present
            foreach ($this->_viewdefs['panels'] as $loop_panelID => $panel_contents) {
                foreach ($panel_contents as $row_id => $row) {
                    foreach ($row as $col_id => $col) {
                        if ($col['name'] == '(filler)') {
                            $this->_viewdefs['panels'][$loop_panelID][$row_id][$col_id] = null;
                        } elseif ($col['name'] == '(empty)') {
                            unset($this->_viewdefs['panels'][$loop_panelID][$row_id][$col_id]);
                        }
                    }
                }
            }

            $panel = $this->_viewdefs ['panels'] [$panelID];
            $lastrow = safeCount($panel) - 1; // index starts at 0
            $lastcol = safeCount($panel[$lastrow]);

            // if we're on the last column of the last row, start a new row
            //          print "lastrow=$lastrow lastcol=$lastcol";
            if ($lastcol >= $this->maxColumns) {
                $lastrow++;
                $this->_viewdefs ['panels'] [$panelID] [$lastrow] = [];
                $lastcol = 0;
            }

            $this->_viewdefs ['panels'] [$panelID] [$lastrow] [$lastcol] = $properties;
        }
    }

    /* getModelFields returns an array of all fields stored in the database for this module plus those fields in the original layout definition (so we get fields such as Team ID)*/
    // @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore
    public function _getModelFields()
    {
        $modelFields = [];
        $origViewDefs = $this->_getOrigFieldViewDefs();
        //        $GLOBALS['log']->debug("Original viewdefs = ".print_r($origViewDefs,true));
        foreach ($origViewDefs as $field => $def) {
            if (!empty($field)) {
                if (!is_array($def)) {
                    $def = ['name' => $field];
                }
                // get this field's label - if it has not been explicitly provided, see if the fieldDefs has a label for this field, and if not fallback to the field name
                if (!isset($def ['label'])) {
                    if (!empty($this->_fieldDefs [$field] ['vname'])) {
                        $def ['label'] = $this->_fieldDefs [$field] ['vname'];
                    } else {
                        $def ['label'] = $field;
                    }
                }
                $modelFields[$field] = ['name' => $field, 'label' => $def ['label']];
            }
        }
        $GLOBALS['log']->debug(print_r($modelFields, true));
        foreach ($this->_fieldDefs as $field => $def) {
            if ((!empty($def['studio']) && $def['studio'] == 'visible')
                || (empty($def['studio']) && (empty($def ['source']) || $def ['source'] == 'db' || $def ['source'] == 'custom_fields') && $def ['type'] != 'id' && strcmp($field, 'deleted') != 0 && (empty($def ['dbType']) || $def ['dbType'] != 'id') && (empty($def ['dbtype']) || $def ['dbtype'] != 'id'))) {
                $label = $def['vname'] ?? $def['name'];
                $modelFields [$field] = ['name' => $field, 'label' => $label];
            } else {
                $GLOBALS['log']->debug(get_class($this) . "->_getModelFields(): skipping $field from modelFields as it fails the test for inclusion");
            }
        }
        $GLOBALS['log']->debug(get_class($this) . '->_getModelFields(): remaining entries in modelFields are: ' . implode(',', array_keys($modelFields)));
        return $modelFields;
    }

    // @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore
    public function _parseData($panels)
    {
        $displayData = [];
        $fields = [];
        if (empty($panels)) {
            return;
        }

        // Fix for a flexibility in the format of the panel sections - if only one panel, then we don't have a panel level defined, it goes straight into rows
        // See EditView2 for similar treatment
        if (!empty($panels) && safeCount($panels) > 0) {
            $keys = array_keys($panels);
            if (is_numeric($keys [0])) {
                $defaultPanel = $panels;
                unset($panels); //blow away current value
                $panels [''] = $defaultPanel;
            }
        }

        foreach ($panels as $panelID => $panel) {
            foreach ($panel as $rowID => $row) {
                foreach ($row as $colID => $col) {
                    $properties = [];

                    if (!empty($col)) {
                        if (is_string($col)) {
                            $properties ['name'] = $col;
                        } elseif (!empty($col ['name'])) {
                            $properties = $col;
                        }
                    } else {
                        $properties ['name'] = translate('LBL_FILLER');
                    }

                    if (!empty($properties ['name'])) {
                        // get this field's label - if it has not been explicity provided, see if the fieldDefs has a label for this field, and if not fallback to the field name
                        if (!isset($properties ['label'])) {
                            if (!empty($this->_fieldDefs [$properties ['name']] ['vname'])) {
                                $properties ['label'] = $this->_fieldDefs [$properties ['name']] ['vname'];
                            } else {
                                $properties ['label'] = $properties ['name'];
                            }
                        }

                        $displayData[strtoupper($panelID)] [$rowID] [$colID] = $properties;
                    }
                }
            }
        }
        return $displayData;
    }

    // @codingStandardsIgnoreLine PSR2.Methods.MethodDeclaration.Underscore
    public function _getOrigFieldViewDefs()
    {
        $viewdefs = [];
        $origFieldDefs = [];
        $GLOBALS['log']->debug('Original File = ' . $this->_originalFile);
        if (file_exists($this->_originalFile)) {
            include $this->_originalFile;
            $origdefs = $viewdefs [$this->_module] [$this->_sourceView] ['panels'];
            //          $GLOBALS['log']->debug($origdefs);
            // Fix for a flexibility in the format of the panel sections - if only one panel, then we don't have a panel level defined, it goes straight into rows
            // See EditView2 for similar treatment
            if (!empty($origdefs) && safeCount($origdefs) > 0) {
                $keys = array_keys($origdefs);
                if (is_numeric($keys [0])) {
                    $defaultPanel = $origdefs;
                    unset($origdefs); //blow away current value
                    $origdefs [''] = $defaultPanel;
                }
            }
            foreach ($origdefs as $pname => $paneldef) {
                foreach ($paneldef as $row) {
                    foreach ($row as $fieldDef) {
                        if (is_array($fieldDef)) {
                            $fieldName = $fieldDef ['name'];
                        } else {
                            $fieldName = $fieldDef;
                        }
                        $origFieldDefs [$fieldName] = $fieldDef;
                    }
                }
            }
        }

        return $origFieldDefs;
    }
}
