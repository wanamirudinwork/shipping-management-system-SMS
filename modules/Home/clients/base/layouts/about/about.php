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

$viewdefs['Home']['base']['layout']['about'] = [
    'css_class' => 'row-fluid about-page bg-[--foreground-base] h-full w-full',
    'components' => [
        [
            'layout' => [
                'css_class' => 'main-pane span12 bg-transparent',
                'components' => [
                    [
                        'view' => 'about-headerpane',
                    ],
                    [
                        'layout' => [
                            'type' => 'fluid',
                            'components' => [
                                [
                                    'view' => [
                                        'type' => 'about-version',
                                        'span' => 12,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'layout' => [
                            'type' => 'fluid',
                            'components' => [
                                [
                                    'view' => [
                                        'type' => 'about-copyright',
                                        'span' => 12,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'layout' => [
                            'type' => 'fluid',
                            'components' => [
                                [
                                    'view' => [
                                        'type' => 'about-resources',
                                        'span' => 6,
                                    ],
                                ],
                                [
                                    'view' => [
                                        'type' => 'about-source-code',
                                        'span' => 6,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
