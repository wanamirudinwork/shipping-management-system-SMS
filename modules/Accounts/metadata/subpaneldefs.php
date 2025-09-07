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

$layout_defs['Accounts'] = [
    // list of what Subpanels to show in the DetailView
    'subpanel_setup' => [

        'activities' => [
            'order' => 10,
            'sort_order' => 'desc',
            'sort_by' => 'date_start',
            'title_key' => 'LBL_ACTIVITIES_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'activities',   //this values is not associated with a physical file.
            'header_definition_from_subpanel' => 'meetings',
            'module' => 'Activities',

            'top_buttons' => [
                ['widget_class' => 'SubPanelTopCreateTaskButton'],
                ['widget_class' => 'SubPanelTopScheduleMeetingButton'],
                ['widget_class' => 'SubPanelTopScheduleCallButton'],
                ['widget_class' => 'SubPanelTopComposeEmailButton'],
            ],

            'collection_list' => [
                'tasks' => [
                    'module' => 'Tasks',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'tasks',
                ],
                'meetings' => [
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'meetings',
                ],
                'calls' => [
                    'module' => 'Calls',
                    'subpanel_name' => 'ForActivities',
                    'get_subpanel_data' => 'calls',
                ],
            ],
        ],
        'history' => [
            'order' => 20,
            'sort_order' => 'desc',
            'sort_by' => 'date_entered',
            'title_key' => 'LBL_HISTORY_SUBPANEL_TITLE',
            'type' => 'collection',
            'subpanel_name' => 'history',   //this values is not associated with a physical file.
            'header_definition_from_subpanel' => 'meetings',
            'module' => 'History',

            'top_buttons' => [
                ['widget_class' => 'SubPanelTopCreateNoteButton'],
                ['widget_class' => 'SubPanelTopArchiveEmailButton'],
                ['widget_class' => 'SubPanelTopSummaryButton'],
            ],

            'collection_list' => [
                'tasks' => [
                    'module' => 'Tasks',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'tasks',
                ],
                'meetings' => [
                    'module' => 'Meetings',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'meetings',
                ],
                'calls' => [
                    'module' => 'Calls',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'calls',
                ],
                'notes' => [
                    'module' => 'Notes',
                    'subpanel_name' => 'ForHistory',
                    'get_subpanel_data' => 'notes',
                ],
                'emails' => [
                    'module' => 'Emails',
                    'subpanel_name' => 'ForUnlinkedEmailHistory',
                    'get_subpanel_data' => 'function:get_emails_by_assign_or_link',
                    'function_parameters' => ['import_function_file' => 'include/utils.php', 'link' => 'contacts'],
                    'generate_select' => true,
                    'get_distinct_data' => true,
                ],
            ],
        ],
        'documents' => [
            'order' => 25,
            'module' => 'Documents',
            'subpanel_name' => 'default',
            'sort_order' => 'asc',
            'sort_by' => 'id',
            'title_key' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
            'get_subpanel_data' => 'documents',
            'top_buttons' => [
                0 =>
                    [
                        'widget_class' => 'SubPanelTopButtonQuickCreate',
                    ],
                1 =>
                    [
                        'widget_class' => 'SubPanelTopSelectButton',
                        'mode' => 'MultiSelect',
                    ],
            ],
        ],
        'contacts' => [
            'order' => 30,
            'module' => 'Contacts',
            'sort_order' => 'asc',
            'sort_by' => 'last_name, first_name',
            'subpanel_name' => 'ForAccounts',
            'get_subpanel_data' => 'contacts',
            'add_subpanel_data' => 'contact_id',
            'title_key' => 'LBL_CONTACTS_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopCreateAccountNameButton'],
                ['widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect'],
            ],

        ],
        'opportunities' => [
            'order' => 40,
            'module' => 'Opportunities',
            'subpanel_name' => 'ForAccounts',
            'sort_order' => 'desc',
            'sort_by' => 'date_closed',
            'get_subpanel_data' => 'opportunities',
            'add_subpanel_data' => 'opportunity_id',
            'title_key' => 'LBL_OPPORTUNITIES_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopButtonQuickCreate'],
            ],
        ],
        'leads' => [
            'order' => 80,
            'module' => 'Leads',
            'sort_order' => 'asc',
            'sort_by' => 'last_name, first_name',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'leads',
            'add_subpanel_data' => 'lead_id',
            'title_key' => 'LBL_LEADS_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopCreateLeadNameButton'],
                ['widget_class' => 'SubPanelTopSelectButton',
                    'popup_module' => 'Opportunities',
                    'mode' => 'MultiSelect',
                ],
            ],

        ],
        'cases' => [
            'order' => 100,
            'sort_order' => 'desc',
            'sort_by' => 'case_number',
            'module' => 'Cases',
            'subpanel_name' => 'ForAccounts',
            'get_subpanel_data' => 'cases',
            'add_subpanel_data' => 'case_id',
            'title_key' => 'LBL_CASES_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopButtonQuickCreate'],
                ['widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect'],
            ],
        ],
        'products' => [
            'order' => 60,
            'module' => 'Products',
            'subpanel_name' => 'ForAccounts',
            'sort_order' => 'desc',
            'sort_by' => 'date_purchased',
            'title_key' => 'LBL_PRODUCTS_SUBPANEL_TITLE',
            'get_subpanel_data' => 'products',
            'top_buttons' => [
                [
                    'widget_class' => 'SubPanelTopButtonQuickCreate',
                ],
                [
                    'widget_class' => 'SubPanelTopSelectButton',
                    'mode' => 'MultiSelect',
                ],
            ],

        ],
        'quotes' => [
            'order' => 50,
            'sort_order' => 'desc',
            'sort_by' => 'date_quote_expected_closed',
            'module' => 'Quotes',
            'subpanel_name' => 'ForAccounts',
            'get_subpanel_data' => 'quotes',
            'get_distinct_data' => true,
            'add_subpanel_data' => 'quote_id',
            'title_key' => 'LBL_QUOTES_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopCreateButton'],
            ],
        ],
        'accounts' => [
            'order' => 90,
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'module' => 'Accounts',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'members',
            'add_subpanel_data' => 'member_id',
            'title_key' => 'LBL_MEMBER_ORG_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopButtonQuickCreate'],
                ['widget_class' => 'SubPanelTopSelectAccountButton', 'mode' => 'MultiSelect'],
            ],
        ],
        'bugs' => [
            'order' => 110,
            'sort_order' => 'desc',
            'sort_by' => 'bug_number',
            'module' => 'Bugs',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'bugs',
            'add_subpanel_data' => 'bug_id',
            'title_key' => 'LBL_BUGS_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopButtonQuickCreate'],
                ['widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect'],
            ],
        ],
        'project' => [
            'order' => 120,
            'sort_order' => 'asc',
            'sort_by' => 'name',
            'module' => 'Project',
            'subpanel_name' => 'default',
            'get_subpanel_data' => 'project',
            'add_subpanel_data' => 'project_id',
            'title_key' => 'LBL_PROJECTS_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopButtonQuickCreate'],
                ['widget_class' => 'SubPanelTopSelectButton', 'mode' => 'MultiSelect'],
            ],
        ],
        'campaigns' => [
            'order' => 70,
            'module' => 'CampaignLog',
            'sort_order' => 'desc',
            'sort_by' => 'activity_date',
            'get_subpanel_data' => 'campaigns',
            'subpanel_name' => 'ForTargets',
            'title_key' => 'LBL_CAMPAIGNS',
        ],
        'contracts' => [
            'order' => 70,
            'sort_order' => 'desc',
            'sort_by' => 'end_date',
            'module' => 'Contracts',
            'subpanel_name' => 'ForAccounts',
            'get_subpanel_data' => 'contracts',
            'add_subpanel_data' => 'contract_id',
            'title_key' => 'LBL_CONTRACTS_SUBPANEL_TITLE',
            'top_buttons' => [
                ['widget_class' => 'SubPanelTopButtonQuickCreate'],
            ],
        ],
        // Market
        'sf_webactivity_accounts' => [
            'order' => 100,
            'module' => 'sf_webActivity',
            'subpanel_name' => 'default',
            'sort_order' => 'asc',
            'sort_by' => 'id',
            'title_key' => 'LBL_SF_WEBACTIVITY_ACCOUNTS_FROM_SF_WEBACTIVITY_TITLE',
            'get_subpanel_data' => 'sf_webactivity_accounts',
            'top_buttons' => [],
        ],
    ],
];
