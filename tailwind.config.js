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
const SugarColorPalette = require('./maple-syrup/build/color/tailwind/sugarPalette');
const SugarBorderRadius = require('./maple-syrup/build/size/tailwind/borderRadius');
const baseUrl = '../../../styleguide/assets';

module.exports = {
    blocklist: [
        'collapse', // Collides with Bootstrap collapse plugin
    ],
    content: [
        './styleguide/tailwind.css',
        './clients/base/**/*.{hbs,js,php}',
        './clients/base/**/**/*.{hbs,js,php}',
        './modules/**/*.{hbs,js,php}',
        './modules/**/clients/base/**/**/*.{hbs,js,php}',
        './include/MVC/View/tpls/sidecar.tpl',
    ],
    darkMode: ['class', '.sugar-dark-theme'],
    theme: {
        colors: SugarColorPalette,
        borderRadius: SugarBorderRadius,
        boxShadow: {
            'none': '0 0 0 var(--shadow-color)',
            DEFAULT: '0 2px 2px var(--shadow-color), 0 0 2px var(--shadow-color)',
            'md': '0 2px 4px var(--shadow-color)',
            'lg': '0 2px 8px var(--shadow-color)',
            'xl': '0 2px 9px var(--shadow-color)'
        },
        extend: {
            boxShadow: {
                'no-top': '0 2px 2px var(--shadow-color)'
            },
            fontSize: {
                '2xs': ['0.625rem', {
                    lineHeight: '0.75rem'
                }]
            },
            leading: {
                '3': ['0.625rem', {
                    lineHeight: '0.625rem'
                }]
            },
            paddingBottom: {
                '0.75': ['0.1875rem', {
                    paddingBottom: '0.1875rem',
                }]
            },
            width: {
                '15': ['3.75rem', {
                    width: '3.75rem',
                }],
                '50': ['12.5rem', {
                    width: '12.5rem',
                }]
            },
            spacing: {
                '0.75': '0.1875rem',
                '25': '6.25rem',
                '26': '6.5rem',
                '27': '6.75rem',
                '15': '3.75rem',
            },
            backgroundImage: {
                'skl-column-total': `url('${baseUrl}/img/skeleton-loaders/tile-view/light/column-total.svg')`,
                'skl-column-total-dark': `url('${baseUrl}/img/skeleton-loaders/tile-view/dark/column-total.svg')`,
                'skl-record': `url('${baseUrl}/img/skeleton-loaders/tile-view/light/record.svg')`,
                'skl-record-dark': `url('${baseUrl}/img/skeleton-loaders/tile-view/dark/record.svg')`,
            },
        },
    },
    plugins: [],
    corePlugins: {
        preflight: false, // Enable once more of our own preflight and reset styles are cleaned up
    }
};
