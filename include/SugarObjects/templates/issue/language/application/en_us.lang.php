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
$object_name = strtolower($object_name);
$app_list_strings = [

    $object_name . '_type_dom' => [
        'Administration' => 'Administration',
        'Product' => 'Product',
        'User' => 'User',
    ],
    $object_name . '_status_dom' => [
        'New' => 'New',
        'Assigned' => 'Assigned',
        'Closed' => 'Closed',
        'Pending Input' => 'Pending Input',
        'Rejected' => 'Rejected',
        'Duplicate' => 'Duplicate',
    ],
    $object_name . '_priority_dom' => [
        'P1' => 'High',
        'P2' => 'Medium',
        'P3' => 'Low',
    ],
    $object_name . '_resolution_dom' => [
        '' => '',
        'Accepted' => 'Accepted',
        'Duplicate' => 'Duplicate',
        'Closed' => 'Closed',
        'Out of Date' => 'Out of Date',
        'Invalid' => 'Invalid',
    ],
];
