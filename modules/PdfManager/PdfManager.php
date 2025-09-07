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


require_once 'modules/PdfManager/PdfManagerHelper.php';

class PdfManager extends Basic
{
    public $new_schema = true;
    public $module_dir = 'PdfManager';
    public $object_name = 'PdfManager';
    public $table_name = 'pdfmanager';
    public $importable = false;
    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $team_id;
    public $team_set_id;
    public $team_count;
    public $team_name;
    public $team_link;
    public $team_count_link;
    public $teams;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $base_module;
    public $published;
    public $field;
    public $body_html;
    public $template_name;
    public $title;
    public $subject;
    public $keywords;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    public function get_list_view_data($filter_fields = [])
    {
        $the_array = parent::get_list_view_data();
        $the_array['BASE_MODULE'] = PdfManagerHelper::getModuleName($this->base_module);

        return $the_array;
    }

    public function deleteAttachment($isduplicate = 'false')
    {
        if (!$this->canEdit()) {
            return false;
        }

        if ($isduplicate === 'true') {
            return true;
        }

        $removeFile = $this->getRemoveFilePath();

        if ($this->deleteFile($removeFile)) {
            $this->updateHeaderLogoAndSave();
            return true;
        }

        return false;
    }

    protected function canEdit(): bool
    {
        return $this->ACLAccess('edit');
    }

    protected function getRemoveFilePath(): string
    {
        return "upload://{$this->id}";
    }

    protected function deleteFile(string $file): bool
    {
        if (file_exists($file) && unlink($file)) {
            return true;
        } else {
            $GLOBALS['log']->error("*** Could not unlink() file: [ {$file} ]");
            return false;
        }
    }

    protected function updateHeaderLogoAndSave(): void
    {
        $this->header_logo = '';

        if ($this->id) {
            $this->save();
        }
    }
}
