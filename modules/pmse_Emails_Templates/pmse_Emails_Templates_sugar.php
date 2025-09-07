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

class pmse_Emails_Templates_sugar extends Basic
{
    public $new_schema = true;
    public $module_name = 'pmse_Emails_Templates';
    public $module_dir = 'pmse_Emails_Templates';
    public $object_name = 'pmse_Emails_Templates';
    public $table_name = 'pmse_emails_templates';
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
    public $from_name;
    public $from_address;
    public $subject;
    public $body;
    public $body_html;
    public $type;
    public $base_module;
    public $text_only;
    public $published;
    public $disable_row_level_security = true;

    public const CURRENT_ACTIVITY_LINK = 'current_activity';


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
