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

use Sugarcrm\Sugarcrm\Cache\Backend\InMemory;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\SystemProcessLock\SystemProcessLock;
use Sugarcrm\Sugarcrm\Util\Files\FileLoader;

/**
 * Language files management
 * @api
 */
class LanguageManager
{
    protected static $createdModules = [];

    /**
     * Called from VardefManager to allow for caching a lang file for a module
     * @param module - the name of the module we are working with
     * @param templates - an array of templates this module uses
     */
    public static function createLanguageFile($module, $templates = ['default'], $refresh = false)
    {
        global $mod_strings, $current_language;
        if (inDeveloperMode() || !empty($_SESSION['developerMode'])) {
            $refresh = true;
        }
        $temp_mod_strings = $mod_strings;
        $lang = $current_language;
        if (empty($lang)) {
            $lang = $GLOBALS['sugar_config']['default_language'];
        }

        $cachedFile = sugar_cached('modules/') . $module . '/language/' . $lang . '.lang.php';
        if (empty(self::$createdModules[$module]) && ($refresh || !file_exists($cachedFile))) {
            $loaded_mod_strings = [];
            $loaded_mod_strings = LanguageManager::loadTemplateLanguage($module, $templates, $lang, $loaded_mod_strings);
            self::$createdModules[$module] = true;

            self::refreshLanguageSafe(
                (string)$module,
                (string)$lang,
                (bool)$refresh,
                $cachedFile,
                (array)$loaded_mod_strings
            );
        }
    }

    /**
     * Resets the created modules array so that in-memory vardef rebuilds can
     * successfully pull of the refresh of metadata
     */
    public static function resetCreatedModules()
    {
        self::$createdModules = [];
    }

    /**
     * Load the module  tempalte lauguage files
     * @param module - the name of the module we are working with
     * @param templates - an array of templates this module uses
     * @param lang - current language this module use
     * @param loaded_mod_strings - the string that we will add the module template language  into
     */
    public static function loadTemplateLanguage($module, $templates, $lang, $loaded_mod_strings)
    {
        $templates = array_reverse($templates);
        foreach ($templates as $template) {
            $temp = LanguageManager::addTemplate($module, $lang, $template);
            $loaded_mod_strings = sugarLangArrayMerge($loaded_mod_strings, $temp);
        }
        return $loaded_mod_strings;
    }

    public static function addTemplate($module, $lang, $template)
    {
        if ($template == 'default') {
            $template = 'basic';
        }
        $templates = [];
        $fields = [];
        if (empty($templates[$template])) {
            foreach (SugarAutoLoader::existing(
                'include/SugarObjects/templates/' . $template . '/language/' . $lang . '.lang.php',
                'include/SugarObjects/implements/' . $template . '/language/' . $lang . '.lang.php'
            ) as $path) {
                require $path;
                $templates[$template] = $mod_strings;
            }
        }
        if (!empty($templates[$template])) {
            return $templates[$template];
        }
    }

    public static function saveCache($module, $lang, $loaded_mod_strings, $additonal_objects = [])
    {
        if (empty($lang)) {
            $lang = $GLOBALS['sugar_config']['default_language'];
        }

        $file = create_cache_directory('modules/' . $module . '/language/' . $lang . '.lang.php');
        write_array_to_file('mod_strings', $loaded_mod_strings, $file);
        include FileLoader::validateFilePath($file);

        // put the item in the sugar cache.
        $key = self::getLanguageCacheKey($module, $lang);
        Container::getInstance()->get(InMemory::class)->set($key, $loaded_mod_strings);
    }

    /**
     * clear out the language cache.
     * @param string module_dir the module_dir to clear, if not specified then clear
     *                      clear language cache for all modules.
     * @param string lang the name of the object we are clearing this is for sugar_cache
     */
    public static function clearLanguageCache($module_dir = '', $lang = '')
    {
        if (empty($lang)) {
            $languages = array_keys($GLOBALS['sugar_config']['languages']);
        } else {
            $languages = [$lang];
        }

        if (!empty($module_dir)) {
            self::clearLanguageCacheForModule($module_dir, $languages);
        } else {
            self::clearLanguageCacheForAllModules($languages);
        }
    }

    /**
     * Clear language cache for a specific module.
     * @param string module_dir the module_dir to clear
     * @param array languages the languages to clear
     */
    private static function clearLanguageCacheForModule($module_dir, $languages)
    {
        foreach ($languages as $clean_lang) {
            self::clearCache($module_dir, $clean_lang);
        }
    }

    /**
     * Clear language cache for all modules.
     * @param array languages the languages to clear
     */
    private static function clearLanguageCacheForAllModules($languages)
    {
        $cache_dir = sugar_cached('modules');
        if (file_exists($cache_dir)) {
            foreach (glob("{$cache_dir}/*", GLOB_ONLYDIR | GLOB_NOSORT) as $entry) {
                $module = basename($entry);
                self::clearLanguageCacheForModule($module, $languages);
            }
        }
    }

    /**
     * PRIVATE function used within clearLanguageCache so we do not repeat logic
     * @param string module_dir the module_dir to clear
     * @param string lang the name of the language file we are clearing this is for sugar_cache
     */
    private static function clearCache(string $module_dir, string $lang): void
    {
        if (!empty($module_dir) && !empty($lang)) {
            $file = sugar_cached('modules/') . $module_dir . '/language/' . $lang . '.lang.php';
            if (file_exists($file)) {
                unlink($file);
            }
            $key = self::getLanguageCacheKey($module_dir, $lang);
            sugar_cache_clear($key);
            Container::getInstance()->get(InMemory::class)->delete($key);
            $key = "return_mod_lang_{$module_dir}_{$lang}";
            sugar_cache_clear($key);
            Container::getInstance()->get(InMemory::class)->delete($key);
        }
    }

    /**
     * Gets the list of file paths to load for languages for a module in the order
     * that they should be loaded.
     *
     * DO NOT EVER CHANGE THE ORDER OF THESE FILES. EVER. SERIOUSLY, DON'T DO IT.
     *
     * @param string $module The name of the module to get the paths for
     * @param string $lang The language to get the files for
     * @return array
     */
    public static function getModuleLanguageFilePaths($module, $lang)
    {
        return [
            'modules/' . $module . '/language/' . $lang . '.lang.php',
            'modules/' . $module . '/language/' . $lang . '.lang.override.php',
            'custom/modules/' . $module . '/language/' . $lang . '.lang.php',
            'custom/modules/' . $module . '/Ext/Language/' . $lang . '.lang.ext.php',
        ];
    }

    /**
     * Given a module, search all of the specified locations, and any others as specified
     * in order to refresh the cache file
     *
     * @param string $module the given module we want to load the vardefs for
     * @param string $lang the given language we wish to load
     * @param array $additional_search_paths an array which allows a consumer to pass in additional vardef locations to search
     */
    public static function refreshLanguage(
        $module,
        $lang,
        $loaded_mod_strings = [],
        $additional_search_paths = null
    ) {
        // Some of the vardefs do not correctly define dictionary as global.  Declare it first.
        $lang_paths = self::getModuleLanguageFilePaths($module, $lang);
        $object = BeanFactory::getObjectName($module);
        if ($object && !empty($GLOBALS['dictionary'][$object]['templates'])) {
            $templates = $GLOBALS['dictionary'][$object]['templates'];
            $loaded_mod_strings = LanguageManager::loadTemplateLanguage(
                $module,
                $templates,
                $lang,
                $loaded_mod_strings
            );
        }

        // Add in additional search paths if they were provided.
        if (!empty($additional_search_paths) && is_array($additional_search_paths)) {
            $lang_paths = array_merge($lang_paths, $additional_search_paths);
        }

        //search a predefined set of locations for the vardef files
        foreach (SugarAutoLoader::existing($lang_paths) as $path) {
            require $path;
            if (!empty($mod_strings)) {
                if (function_exists('sugarArrayMergeRecursive')) {
                    $loaded_mod_strings = sugarArrayMergeRecursive($loaded_mod_strings, $mod_strings);
                } else {
                    $loaded_mod_strings = sugarLangArrayMerge($loaded_mod_strings, $mod_strings);
                }
            }
        }

        //great! now that we have loaded all of our vardefs.
        //let's go save them to the cache file.
        if (!empty($loaded_mod_strings)) {
            LanguageManager::saveCache($module, $lang, $loaded_mod_strings);
        }
    }

    public static function loadModuleLanguage($module, $lang, $refresh = false)
    {
        //here check if the cache file exists, if it does then load it, if it doesn't
        //then call refreshVardef
        //if either our session or the system is set to developerMode then refresh is set to true

        // Retrieve the vardefs from cache.
        $key = self::getLanguageCacheKey($module, $lang);

        if (!$refresh) {
            $return_result = Container::getInstance()->get(InMemory::class)->get($key);
            if (!empty($return_result) && is_array($return_result)) {
                return $return_result;
            }
            if ($return_result === SugarCache::EXTERNAL_CACHE_NULL_VALUE) {
                return [];
            }
        }

        // Some of the vardefs do not correctly define dictionary as global.  Declare it first.
        $cachedfile = sugar_cached('modules/') . $module . '/language/' . $lang . '.lang.php';

        if (!check_file_name($cachedfile)) {
            throw new \Exception('Path traversal attack vector detected');
        }

        if ($refresh || !file_exists($cachedfile)) {
            self::refreshLanguageSafe((string)$module, (string)$lang, (bool)$refresh, $cachedfile);
        }

        //at this point we should have the cache/modules/... file
        //which was created from the refreshVardefs so let's try to load it.
        if (file_exists($cachedfile)) {
            global $mod_strings;

            include $cachedfile;

            // now that we hae loaded the data from disk, put it in the cache.
            if (!empty($mod_strings)) {
                Container::getInstance()->get(InMemory::class)->set($key, $mod_strings);
            }
            if (!empty($_SESSION['translation_mode'])) {
                $mod_strings = array_map('translated_prefix', $mod_strings);
            }
            return $mod_strings;
        }
    }

    /**
     * Return the cache key for the module language definition
     *
     * @static
     * @param  $module
     * @param  $lang
     * @return string
     */
    public static function getLanguageCacheKey($module, $lang)
    {
        return "LanguageManager.$module.$lang";
    }

    /**
     * Remove any cached js language strings.
     *
     * @static
     * @return void
     */
    public static function removeJSLanguageFiles()
    {
        $jsFiles = [];
        getFiles($jsFiles, sugar_cached('jsLanguage'));
        foreach ($jsFiles as $file) {
            unlink($file);
        }

        self::invalidateJsLanguageCache();
    }

    /**
     * Invalidates javascript language cache
     */
    public static function invalidateJsLanguageCache()
    {
        $configurator = new Configurator();
        $configurator->setAllowKeys(['js_lang_version']);
        $jsLangVersion = (int)($configurator->config['js_lang_version'] ?? 0);
        $jsLangVersion++;
        $configurator->config['js_lang_version'] = $jsLangVersion;
        $configurator->handleOverride();
        SugarConfig::getInstance()->clearCache();
    }

    /**
     * Gets an array of enabled and disabled languages
     *
     * @return array Array containing an 'enabled' and 'disabled' set of arrays
     */
    public static function getEnabledAndDisabledLanguages()
    {
        global $sugar_config;

        $enabled = $disabled = [];
        $disabled_list = [];
        if (isset($sugar_config['disabled_languages'])) {
            if (!is_array($sugar_config['disabled_languages'])) {
                $disabled_list = array_flip(explode(',', $sugar_config['disabled_languages']));
            } else {
                $disabled_list = array_flip($sugar_config['disabled_languages']);
            }
        }

        foreach ($sugar_config['languages'] as $key => $value) {
            if (isset($disabled_list[$key])) {
                $disabled[] = ['module' => $key, 'label' => $value];
            } else {
                $enabled[] = ['module' => $key, 'label' => $value];
            }
        }

        return ['enabled' => $enabled, 'disabled' => $disabled];
    }

    protected static function refreshLanguageSafe(
        string $module,
        string $lang,
        bool   $needRefresh,
        string $cachedFile,
        array  $loadedModStrings = []
    ): void {
        $checkCondition = function () use ($needRefresh, $cachedFile) {
            if ($needRefresh) {
                return true;
            }
            return !file_exists($cachedFile);
        };

        $longRunningFunction = function (int $attempt) use ($module, $lang, $loadedModStrings) {
            LanguageManager::refreshLanguage($module, $lang, $loadedModStrings);
        };

        $refuseFunction = $longRunningFunction;

        // the following is designed to prevent process race conditions in a long-running process
        $systemProcessLock = new SystemProcessLock(__METHOD__, $module . '.' . $lang);
        $systemProcessLock->isolatedCall($checkCondition, $longRunningFunction, $refuseFunction);
    }
}

function translated_prefix($key)
{
    return '[translated]' . $key;
}
