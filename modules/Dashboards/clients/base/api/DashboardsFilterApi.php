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

use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;

class DashboardsFilterApi extends FilterApi
{
    public function registerApiRest()
    {
        return [
            'filterModuleGet' => [
                'reqType' => 'GET',
                'path' => ['Dashboards', 'filter'],
                'pathVars' => ['module', ''],
                'method' => 'filterList',
                'jsonParams' => ['filter'],
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
                'exceptions' => [
                    // Thrown in getPredefinedFilterById
                    'SugarApiExceptionNotFound',
                    'SugarApiExceptionError',
                    // Thrown in filterList and filterListSetup
                    'SugarApiExceptionInvalidParameter',
                    // Thrown in filterListSetup, getPredefinedFilterById, and parseArguments
                    'SugarApiExceptionNotAuthorized',
                ],
            ],
            'filterModuleAll' => [
                'reqType' => 'GET',
                'path' => ['Dashboards'],
                'pathVars' => ['module'],
                'method' => 'filterList',
                'jsonParams' => ['filter'],
                'shortHelp' => 'List of all records in this module',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
                'exceptions' => [
                    // Thrown in getPredefinedFilterById
                    'SugarApiExceptionNotFound',
                    'SugarApiExceptionError',
                    // Thrown in filterList and filterListSetup
                    'SugarApiExceptionInvalidParameter',
                    // Thrown in filterListSetup, getPredefinedFilterById, and parseArguments
                    'SugarApiExceptionNotAuthorized',
                ],
            ],
            'filterModuleAllCount' => [
                'reqType' => 'GET',
                'path' => ['Dashboards', 'count'],
                'pathVars' => ['module', ''],
                'jsonParams' => ['filter'],
                'method' => 'getFilterListCount',
                'shortHelp' => 'List of all records in this module',
                'longHelp' => 'include/api/help/module_filter_get_help.html',
                'exceptions' => [
                    // Thrown in getPredefinedFilterById
                    'SugarApiExceptionNotFound',
                    'SugarApiExceptionError',
                    // Thrown in filterListSetup and getPredefinedFilterById
                    'SugarApiExceptionNotAuthorized',
                    // Thrown in filterListSetup
                    'SugarApiExceptionInvalidParameter',
                ],
            ],
            'filterModulePost' => [
                'reqType' => 'POST',
                'path' => ['Dashboards', 'filter'],
                'pathVars' => ['module', ''],
                'method' => 'filterList',
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_post_help.html',
                'exceptions' => [
                    // Thrown in getPredefinedFilterById
                    'SugarApiExceptionNotFound',
                    'SugarApiExceptionError',
                    // Thrown in filterList and filterListSetup
                    'SugarApiExceptionInvalidParameter',
                    // Thrown in filterListSetup, getPredefinedFilterById, and parseArguments
                    'SugarApiExceptionNotAuthorized',
                ],
            ],
            'filterModulePostCount' => [
                'reqType' => 'POST',
                'path' => ['Dashboards', 'filter', 'count'],
                'pathVars' => ['module', '', ''],
                'method' => 'filterListCount',
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_post_help.html',
                'exceptions' => [
                    // Thrown in getPredefinedFilterById
                    'SugarApiExceptionNotFound',
                    'SugarApiExceptionError',
                    // Thrown in filterListSetup and getPredefinedFilterById
                    'SugarApiExceptionNotAuthorized',
                    // Thrown in filterListSetup
                    'SugarApiExceptionInvalidParameter',
                ],
            ],
            'filterModuleCount' => [
                'reqType' => 'GET',
                'path' => ['Dashboards', 'filter', 'count'],
                'pathVars' => ['module', '', ''],
                'method' => 'getFilterListCount',
                'shortHelp' => 'Lists filtered records.',
                'longHelp' => 'include/api/help/module_filter_post_help.html',
                'exceptions' => [
                    // Thrown in getPredefinedFilterById
                    'SugarApiExceptionNotFound',
                    'SugarApiExceptionError',
                    // Thrown in filterListSetup
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionInvalidParameter',
                ],
            ],
            'filterModuleSum' => [
                'reqType' => 'GET',
                'path' => ['Dashboards', 'total'],
                'pathVars' => ['module', '', ''],
                'method' => 'getFilterListSum',
                'shortHelp' => 'Lists field sum by filtered records.',
                'longHelp' => 'include/api/help/module_sum_by_filter_get_help.html',
                'exceptions' => [
                    // Thrown in getPredefinedFilterById
                    'SugarApiExceptionNotFound',
                    'SugarApiExceptionError',
                    // Thrown in filterListSetup
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionInvalidParameter',
                ],
                'minVersion' => '11.21',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function addFilterByLicense(SugarQuery $query): void
    {
        $acm = AccessControlManager::instance();

        $marketModules = ['sf_Dialogs', 'sf_webActivity', 'sf_EventManagement', 'sf_WebActivityDetail'];
        $marketModules = array_filter($marketModules, function ($module) use ($acm) {
            return !$acm->allowModuleAccess($module);
        });

        if (count($marketModules)) {
            self::addFilter('dashboard_module', ['$not_in' => $marketModules], $query->where(), $query);
        }
    }
}
