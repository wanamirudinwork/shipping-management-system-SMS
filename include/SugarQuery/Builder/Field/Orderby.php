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


/**
 * SugarQuery_Builder_Field_Orderby
 * @api
 */
class SugarQuery_Builder_Field_Orderby extends SugarQuery_Builder_Field
{
    public $direction = 'DESC';
    public $nullsLast = false;

    public function __construct($field, SugarQuery $query, $direction = null, $nullsLast = false)
    {
        $this->direction = $direction;
        $this->nullsLast = $nullsLast;
        parent::__construct($field, $query);
    }

    public function expandField()
    {
        if (!empty($this->def['sort_on'])) {
            $this->def['sort_on'] = !is_array($this->def['sort_on']) ? [$this->def['sort_on']] : $this->def['sort_on'];
        }

        if (!empty($this->def['source']) && $this->def['source'] === 'non-db') {
            $this->markNonDb();
        }

        if (!empty($this->def['rname']) && !empty($this->def['link'])) {
            $jta = $this->query->getJoinAlias($this->def['link']);
            if (empty($jta)) {
                $this->def['link'] = $jta = $this->table;
            }

            $fieldsToOrder = empty($this->def['sort_on']) ? [$this->def['rname']] : $this->def['sort_on'];
            foreach ($fieldsToOrder as $fieldToOrder) {
                // Some sort_on fields are already prefixed with a table name, like
                // in the case of team_name. This cleans that up.
                $field = $this->getTrueFieldNameFromField($fieldToOrder);
                $this->query->orderBy("{$jta}.{$field}", $this->direction, $this->nullsLast);
                if (!$this->query->select->checkField($field, $this->table)) {
                    $this->query->select->addField("{$jta}.{$field}", ['alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->def['link']}__{$field}", false, 'alias')]);
                }
            }

            $this->markNonDb();
        } elseif (!empty($this->def['rname']) && !empty($this->def['table'])) {
            $jta = $this->query->getJoinAlias($this->def['table'], false);
            if (empty($jta)) {
                $jta = empty($this->jta) ? $this->table : $this->jta;
            }

            $fieldsToOrder = empty($this->def['sort_on']) ? [$this->def['rname']] : $this->def['sort_on'];
            foreach ($fieldsToOrder as $fieldToOrder) {
                $field = $this->getTrueFieldNameFromField($fieldToOrder);
                $this->query->orderBy("{$jta}.{$field}", $this->direction, $this->nullsLast);
                if (!$this->query->select->checkField($field, $this->table)) {
                    $this->query->select->addField("{$jta}.{$field}", ['alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->def['table']}__{$field}", false, 'alias')]);
                }
            }

            $this->markNonDb();
        } elseif (!empty($this->def['rname']) && !empty($this->jta)) {
            if (isset($this->def['module'])) {
                $rBean = BeanFactory::getDefinition($this->def['module']);
                if ($rBean?->field_defs[$this->def['rname']]['type'] == 'fullname') {
                    $nameFields = Localization::getObject()->getNameFormatFields($this->def['module']);
                    foreach ($nameFields as $partOfName) {
                        $this->query->orderBy("{$this->jta}.{$partOfName}", $this->direction, $this->nullsLast);
                        if (!$this->query->select->checkField($partOfName, $this->table)) {
                            $this->query->select->addField("{$this->jta}.{$partOfName}", ['alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->jta}__{$partOfName}", false, 'alias')]);
                        }
                    }
                } else {
                    $this->query->orderBy("{$this->jta}.{$this->def['rname']}", $this->direction, $this->nullsLast);
                    if (!$this->query->select->checkField($this->def['rname'], $this->table)) {
                        $this->query->select->addField("{$this->jta}.{$this->def['rname']}", ['alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->jta}__{$this->def['rname']}", false, 'alias')]);
                    }
                }
            }
            $this->markNonDb();
        } elseif (!empty($this->def['rname_link'])) {
            $this->query->orderBy("{$this->table}.{$this->def['rname_link']}", $this->direction, $this->nullsLast);
            if (!$this->query->select->checkField($this->def['rname_link'], $this->table)) {
                $this->query->select->addField("{$this->table}.{$this->def['rname_link']}", ['alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->table}__{$this->def['rname_link']}", false, 'alias')]);
            }
            $this->markNonDb();
        } else {
            if (!empty($this->def['sort_on'])) {
                $table = $this->table;
                //Custom fields may use standard or custom fields for sort on.
                //Let that SugarQuery_Builder_Field figure out if it's custom or not.
                if (!empty($this->custom) && !empty($this->standardTable)) {
                    $table = $this->standardTable;
                }
                foreach ($this->def['sort_on'] as $field) {
                    $this->query->orderBy("{$table}.{$field}", $this->direction, $this->nullsLast);
                    if (!$this->query->select->checkField($field, $this->table)) {
                        $this->query->select->addField("{$table}.{$field}", ['alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->table}__{$this->field}", false, 'alias')]);
                    }
                }
                $this->markNonDb();
            } else {
                if (!$this->query->select->checkField($this->field, $this->table)) {
                    $this->query->select->addField("{$this->table}.{$this->field}", ['alias' => DBManagerFactory::getInstance()->getValidDBName("{$this->table}__{$this->field}", false, 'alias')]);
                }
            }
        }

        $this->checkCustomField();
    }
}
