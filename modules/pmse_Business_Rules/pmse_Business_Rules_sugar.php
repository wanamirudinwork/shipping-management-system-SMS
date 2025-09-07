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


class pmse_Business_Rules_sugar extends Basic
{
    public $new_schema = true;
    public $module_name = 'pmse_Business_Rules';
    public $module_dir = 'pmse_Business_Rules';
    public $object_name = 'pmse_Business_Rules';
    public $table_name = 'pmse_business_rules';
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
    public $activities;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $rst_uid;
    public $rst_type;
    public $rst_definition;
    public $rst_editable;
    public $rst_source;
    public $rst_source_definition;
    public $rst_module;
    public $rst_filename;
    public $rst_create_date;
    public $rst_update_date;
    public $disable_row_level_security = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
