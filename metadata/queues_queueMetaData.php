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
$dictionary['queues_queue'] = ['table' => 'queues_queue',
    'fields' => [
        'id' => [
            'name' => 'id',
            'vname' => 'LBL_QUEUES_QUEUE_ID',
            'type' => 'id',
            'required' => true,
            'reportable' => false,
        ],
        'deleted' => [
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'required' => true,
            'default' => '0',
            'reportable' => false,
        ],
        'date_entered' => [
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_ENTERED',
            'type' => 'datetime',
            'required' => true,
        ],
        'date_modified' => [
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
            'required' => true,
        ],
        'queue_id' => [
            'name' => 'queue_id',
            'vname' => 'LBL_QUEUE_ID',
            'type' => 'id',
            'required' => true,
            'reportable' => false,
        ],
        'parent_id' => [
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_ID',
            'type' => 'id',
            'required' => true,
            'reportable' => false,
        ],
    ],
    'indices' => [
        [
            'name' => 'queues_queuepk',
            'type' => 'primary',
            'fields' => [
                'id',
            ],
        ],
        [
            'name' => 'idx_queue_id',
            'type' => 'index',
            'fields' => [
                'queue_id',
            ],
        ],
        [
            'name' => 'idx_parent_id',
            'type' => 'index',
            'fields' => [
                'parent_id',
            ],
        ],
        [
            'name' => 'compidx_queue_id_parent_id',
            'type' => 'alternate_key',
            'fields' => [
                'queue_id',
                'parent_id',
            ],
        ],
    ], /* end indices */
    'relationships' => [
        'child_queues_rel' => [
            'lhs_module' => 'Queues',
            'lhs_table' => 'queues',
            'lhs_key' => 'id',
            'rhs_module' => 'Queues',
            'rhs_table' => 'queues',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'queues_queue',
            'join_key_lhs' => 'queue_id',
            'join_key_rhs' => 'parent_id',
        ],
        'parent_queues_rel' => [
            'lhs_module' => 'Queues',
            'lhs_table' => 'queues',
            'lhs_key' => 'id',
            'rhs_module' => 'Queues',
            'rhs_table' => 'queues',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'queues_queue',
            'join_key_rhs' => 'queue_id',
            'join_key_lhs' => 'parent_id',
        ],
    ], /* end relationships */
];
