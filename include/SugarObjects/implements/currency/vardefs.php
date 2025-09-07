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
$vardefs = [
    'fields' => [
        'currency_id' => [
            'name' => 'currency_id',
            'dbType' => 'id',
            'vname' => 'LBL_CURRENCY_ID',
            'type' => 'currency_id',
            'function' => 'getCurrencies', // This is needed for BWC modules
            'function_bean' => 'Currencies', // This is needed for BWC modules
            'required' => false,
            'reportable' => false,
            'default' => '-99',
        ],
        'base_rate' => [
            'name' => 'base_rate',
            'vname' => 'LBL_CURRENCY_RATE',
            'type' => 'text',
            'dbType' => 'decimal',
            'len' => '26,6',
            'readonly' => true,
        ],
        'currency_name' => [
            'name' => 'currency_name',
            'rname' => 'name',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_NAME',
            'type' => 'relate',
            'link' => 'currencies',
            'isnull' => true,
            'table' => 'currencies',
            'module' => 'Currencies',
            'source' => 'non-db',
            'studio' => false,
            'duplicate_merge' => 'disabled',
            'function' => 'getCurrencies',
            'function_bean' => 'Currencies',
            'massupdate' => false,
        ],
        'currency_symbol' => [
            'name' => 'currency_symbol',
            'rname' => 'symbol',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_SYMBOL',
            'type' => 'relate',
            'link' => 'currencies',
            'isnull' => true,
            'table' => 'currencies',
            'module' => 'Currencies',
            'source' => 'non-db',
            'studio' => false,
            'duplicate_merge' => 'disabled',
            'function' => 'getCurrencySymbols',
            'function_bean' => 'Currencies',
            'massupdate' => false,
        ],
        'currencies' => [
            'name' => 'currencies',
            'type' => 'link',
            'relationship' => strtolower($module) . '_currencies',
            'source' => 'non-db',
            'vname' => 'LBL_CURRENCIES',
        ],
    ],
    'relationships' => [
        strtolower($module) . '_currencies' => [
            'lhs_module' => 'Currencies',
            'lhs_table' => 'currencies',
            'lhs_key' => 'id',
            'rhs_module' => $module,
            'rhs_table' => strtolower($table_name),
            'rhs_key' => 'currency_id',
            'relationship_type' => 'one-to-many',
        ],
    ],
];
