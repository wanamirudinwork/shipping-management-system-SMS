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
$viewdefs['DRI_Workflow_Task_Templates']['base']['layout']['subpanels'] = [
    'components' => [
        [
            'layout' => 'subpanel',
            'label' => 'LBL_CJ_FORMS_SUBPANEL_TITLE',
            'context' => [
                'link' => 'forms',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_CJ_WEBHOOKS_SUBPANEL_TITLE',
            'context' => [
                'link' => 'web_hooks',
            ],
        ],
    ],
    'type' => 'subpanels',
    'span' => 12,
];
