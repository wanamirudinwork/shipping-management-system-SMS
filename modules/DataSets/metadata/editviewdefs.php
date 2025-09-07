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

$viewdefs['DataSets']['EditView'] = [
    'templateMeta' => ['maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
    ],
    'panels' => [
        'default' => [

            [
                'name',

                [
                    'name' => 'report_name',
                    'customCode' => '<a href="index.php?module=ReportMaker&action=detailview&record={$fields.report_id.value}">{$fields.report_name.value|sugar_escape:\'html\':\'UTF-8\'}</a>&nbsp;',
                ],
            ],

            [
                'query_name',

                [
                    'name' => 'child_name',
                    'customCode' => '{if isset($bean->child_id) && !empty($bean->child_id)}
						 <a href="index.php?module=DataSets&action=DetailView&record={$bean->child_id}">{$bean->child_name}</a>
						 {else}
						 {$bean->child_name}
						 {/if}',
                ],
            ],

            [
                ['name' => 'parent_name',
                    'displayParams' => ['initial_filter' => '&form=EditView&self_id={$fields.id.value}'],
                ],
                'team_name',
            ],

            [

                [
                    'name' => 'description',
                ],
            ],

            [

                [
                    'name' => 'table_width',
                    'fields' => [
                        ['name' => 'table_width', 'displayParams' => ['size' => 3, 'maxlength' => 3]],
                        ['name' => 'table_width_type'],
                    ],
                ],

                'font_size',
            ],

            [
                ['name' => 'exportable',
                    'customCode' => '{if $fields.exportable.value == "1" or $fields.header.value == "on"}
						 {assign var="checked" value="CHECKED"}
						 {else}
						 {assign var="checked" value=""}
						 {/if}
						 <input type="hidden" name="exportable" value="0">
						 <input type="checkbox" id="exportable" name="exportable" value="1" tabindex="{$tabindex}" {$checked}>',],
                'header_text_color',
            ],

            [
                ['name' => 'header',
                    'customCode' => '{if $fields.header.value == "1" or $fields.header.value == "on"}
						 {assign var="checked" value="CHECKED"}
						 {else}
						 {assign var="checked" value=""}
						 {/if}
						 <input type="hidden" name="header" value="0">
						 <input type="checkbox" id="header" name="header" value="1" tabindex="{$tabindex}" {$checked}>',],

                'body_text_color',
            ],

            [
                ['name' => 'prespace_y',
                    'customCode' => '{if $fields.prespace_y.value == "1" or $fields.header.value == "on"}
						 {assign var="checked" value="CHECKED"}
						 {else}
						 {assign var="checked" value=""}
						 {/if}
						 <input type="hidden" name="prespace_y" value="0">
						 <input type="checkbox" id="prespace_y" name="prespace_y" value="1" tabindex="{$tabindex}" {$checked}>',],

                'header_back_color',
            ],

            [
                ['name' => 'use_prev_header',
                    'customCode' => '{if $fields.use_prev_header.value == "1" or $fields.header.value == "on"}
						 {assign var="checked" value="CHECKED"}
						 {else}
						 {assign var="checked" value=""}
						 {/if}
						 <input type="hidden" name="use_prev_header" value="0">
						 <input type="checkbox" id="use_prev_header" name="use_prev_header" value="1" tabindex="{$tabindex}" {$checked}>',],

                'body_back_color',
            ],
        ],
    ],


];
