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
$viewdefs['Leads']['base']['layout']['subpanels'] = [
    'components' => [
        [
            'layout' => 'subpanel',
            'label' => 'LBL_CALLS_SUBPANEL_TITLE',
            'context' => [
                'link' => 'calls',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_MEETINGS_SUBPANEL_TITLE',
            'context' => [
                'link' => 'meetings',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_MESSAGES_SUBPANEL_TITLE',
            'context' => [
                'link' => 'message_invites',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_TASKS_SUBPANEL_TITLE',
            'context' => [
                'link' => 'tasks',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_NOTES_SUBPANEL_TITLE',
            'context' => [
                'link' => 'notes',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE',
            'context' => [
                'link' => 'campaigns',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_EMAILS_SUBPANEL_TITLE',
            'override_subpanel_list_view' => 'subpanel-for-leads-archived-emails',
            'context' => [
                'link' => 'archived_emails',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_DATAPRIVACY_SUBPANEL_TITLE',
            'context' => [
                'link' => 'dataprivacy',
            ],
        ],
        // Market
        [
            'layout' => 'subpanel',
            'label' => 'LBL_SF_DIALOGS_LEADS_FROM_SF_DIALOGS_TITLE',
            'context' => [
                'link' => 'sf_dialogs_leads',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_SF_EVENTMANAGEMENT_LEADS_FROM_LEADS_TITLE',
            'context' => [
                'link' => 'sf_eventmanagement_leads',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_SF_WEB_ACTIVITY_LEADS_FROM_SF_WEB_ACTIVITY_TITLE',
            'context' => [
                'link' => 'sf_webactivity_leads',
            ],
        ],
    ],
];
