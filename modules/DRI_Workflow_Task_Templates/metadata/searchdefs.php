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

$searchdefs['DRI_Workflow_Task_Templates'] = [
    'templateMeta' => [
        'maxColumns' => '3',
        'maxColumnsBasic' => '4',
        'widths' => [
            'label' => '10',
            'field' => '30',
        ],
    ],
    'layout' => [
        'basic_search' => [
            'name' => [
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ],
        ],
        'advanced_search' => [
            'name' => [
                'name' => 'name',
                'default' => true,
                'width' => '10%',
            ],
        ],
    ],
];
