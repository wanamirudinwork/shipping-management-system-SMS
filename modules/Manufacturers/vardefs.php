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
$dictionary['Manufacturer'] = [
    'table' => 'manufacturers',
    'archive' => false,
    'favorites' => false,
    'comment' => 'Manufacturers',
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'fields' => [
        'list_order' => [
            'name' => 'list_order',
            'vname' => 'LBL_LIST_ORDER',
            'type' => 'int',
            'len' => '4',
            'comment' => 'Order within list',
            'importable' => 'required',
        ],
        'status' => [
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'options' => 'manufacturer_status_dom',
            'len' => 100,
            'dbType' => 'varchar',
            'comment' => 'Manufacturer status',
            'importable' => 'required',
        ],
        'revenue_line_items' => [
            'name' => 'revenue_line_items',
            'type' => 'link',
            'relationship' => 'revenuelineitems_manufacturers',
            'source' => 'non-db',
            'vname' => 'LBL_REVENUELINEITEMS',
            'workflow' => false,
        ],
    ],
    'acls' => [
        'SugarACLProduct' => true,
        'SugarACLStatic' => false,
    ],
    'uses' => [
        'basic',
    ],
];

VardefManager::createVardef(
    'Manufacturers',
    'Manufacturer'
);

$dictionary['Manufacturer']['fields']['tag']['massupdate'] = false;
