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

/**
 * cid: scheme implementation
 */
class HTMLPurifier_URIScheme_cid extends HTMLPurifier_URIScheme
{
    public $browsable = true;
    public $may_omit_host = true;

    public function doValidate(&$uri, $config, $context)
    {
        $uri->userinfo = null;
        $uri->port = null;
        $uri->host = null;
        $uri->query = null;
        $uri->fragment = null;
        return true;
    }
}

class HTMLPurifier_Filter_Xmp extends HTMLPurifier_Filter
{
    public $name = 'Xmp';

    public function preFilter($html, $config, $context)
    {
        return preg_replace('#<(/)?xmp>#i', '<\\1pre>', $html);
    }
}

class SugarCleaner
{
    /**
     * Singleton instance
     * @var SugarCleaner
     */
    public static $instance;

    /**
     * HTMLPurifier instance
     * @var HTMLPurifier
     */
    protected $purifier;

    public function __construct()
    {
        global $sugar_config;
        $config = HTMLPurifier_Config::createDefault();

        if (!is_dir(sugar_cached('htmlclean'))) {
            create_cache_directory('htmlclean/');
        }
        $config->set('HTML.AllowedComments', [
            'START_BUNDLE_LOOP' => true,
            'START_PRODUCT_LOOP' => true,
            'END_PRODUCT_LOOP' => true,
            'END_BUNDLE_LOOP' => true,
        ]);
        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
        $config->set('Core.Encoding', 'UTF-8');
        $hidden_tags = ['script' => true, 'style' => true, 'title' => true, 'head' => true];
        $config->set('Core.HiddenElements', $hidden_tags);
        $config->set('Cache.SerializerPath', sugar_cached('htmlclean'));
        $config->set('URI.Base', $sugar_config['site_url']);
        $config->set('CSS.Proprietary', true);
        $config->set('HTML.TidyLevel', 'light');
        $config->set('HTML.ForbiddenElements', ['body' => true, 'html' => true]);
        $config->set('AutoFormat.RemoveEmpty', false);
        $config->set('Cache.SerializerPermissions', 0775);
        // for style
        $config->set('Filter.ExtractStyleBlocks.TidyImpl', false); // can't use csstidy, GPL
        if (!empty($GLOBALS['sugar_config']['html_allow_objects'])) {
            // for object
            $config->set('HTML.SafeObject', true);
            // for embed
            $config->set('HTML.SafeEmbed', true);
        }
        $config->set('Output.FlashCompat', true);
        // for iframe and xmp
        $config->set('Filter.Custom', [new HTMLPurifier_Filter_Xmp()]);
        // for link
        $config->set('HTML.DefinitionID', 'Sugar HTML Def');
        $config->set('HTML.DefinitionRev', 3);
        $config->set('Cache.SerializerPath', sugar_cached('htmlclean/'));
        // IDs are namespaced
        $config->set('Attr.EnableID', true);
        $config->set('Attr.IDPrefix', 'sugar_text_');
        // to allow target attributes for anchor tags
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self', '_parent', '_top']);

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            // Add link tag for custom CSS
            $def->addElement(
                'link',   // name
                'Flow',  // content set
                'Empty', // allowed children
                'Core', // attribute collection
                [ // attributes
                    'href*' => 'URI',
                    'rel' => 'Enum#stylesheet', // only stylesheets supported here
                    'type' => 'Enum#text/css', // only CSS supported here
                ]
            );

            // Add iframe tag
            $iframe = $def->addElement(
                'iframe',   // name
                'Flow',  // content set
                'Optional: #PCDATA | Flow | Block', // allowed children
                'Core', // attribute collection
                [ // attributes
                    'src*' => 'URI',
                    'frameborder' => 'Enum#0,1',
                    'marginwidth' => 'Pixels',
                    'marginheight' => 'Pixels',
                    'scrolling' => 'Enum#|yes,no,auto',
                    'align' => 'Enum#top,middle,bottom,left,right,center',
                    'height' => 'Length',
                    'width' => 'Length',
                ]
            );
            $iframe->excludes = ['iframe'];

            // Add usemap attribute to img tag
            $def->addAttribute('img', 'usemap', 'CDATA');

            // Add map tag
            $map = $def->addElement(
                'map',
                'Block',
                'Flow',
                'Common',
                [
                    'name' => 'CDATA',
                    'id' => 'ID',
                    'title' => 'CDATA',
                ]
            );
            $map->excludes = ['map' => true];

            // Add area tag
            $area = $def->addElement(
                'area',
                'Block',
                'Empty',
                'Common',
                [
                    'name' => 'CDATA',
                    'id' => 'ID',
                    'alt' => 'Text',
                    'coords' => 'CDATA',
                    'accesskey' => 'Character',
                    'nohref' => new HTMLPurifier_AttrDef_Enum(
                        ['nohref']
                    ),
                    'href' => 'URI',
                    'shape' => new HTMLPurifier_AttrDef_Enum(
                        ['rect', 'circle', 'poly', 'default']
                    ),
                    'tabindex' => 'Number',
                    'target' => new HTMLPurifier_AttrDef_Enum(
                        ['_blank', '_self', '_target', '_top']
                    ),
                ]
            );
            $area->excludes = ['area' => true];
        }

        $uri = $config->getDefinition('URI');
        $uri->addFilter(new SugarURIFilter(), $config);
        HTMLPurifier_URISchemeRegistry::instance()->register('cid', new HTMLPurifier_URIScheme_cid());

        $this->purifier = new HTMLPurifier($config);
    }

    /**
     * Get cleaner instance
     * @return SugarCleaner
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Clean string from potential XSS problems
     * @param string $html
     * @param bool $encoded Was it entity-encoded?
     * @return string
     */
    public static function cleanHtml($html, $encoded = false)
    {
        static $processed = [];
        if (!is_scalar($html) || empty($html)) {
            return $html;
        }
        $html = (string)$html;
        $key = md5($html);

        if (in_array($key, $processed)) {
            // mail is already clean, do not process again
            return $html;
        }

        if ($encoded) {
            // take care of multiple html encodings by repeatedly decoding till there is nothing to decode
            do {
                $oldHtml = $html;
                $html = from_html($html);
            } while ($html != $oldHtml);
        }
        if (!preg_match('<[^-A-Za-z0-9 `~!@#$%^&*()_=+{}\[\];:\'",./\\?\r\n|\x80-\xFF]>', $html)) {
            /* if it only has "safe" chars, don't bother */
            $cleanhtml = $html;
        } else {
            $purifier = self::getInstance()->purifier;
            $cleanhtml = self::getCleanhtmlWithWidthPersisntence($html, $purifier);
        }

        if ($encoded) {
            $cleanhtml = to_html($cleanhtml);
        }
        $processed[] = md5($cleanhtml);
        return $cleanhtml;
    }

    public static function stripTags($string, $encoded = true)
    {
        if ($encoded) {
            $string = from_html($string);
        }
        $string = preg_replace('/\x00|<[^>]*>?/', '', $string);
        return $encoded ? to_html($string) : $string;
    }

    private static function getCleanhtmlWithWidthPersisntence(string $html, HTMLPurifier $purifier): string
    {
        //If the width attribute value is in percent and is greated than 100%
        //replace the value with 99999<original width> removing the percent sign
        //to prevent HTMLPurifier from truncating it to 100%
        $processedHtml = preg_replace_callback(
            '/width\s*=\s*"(\d+)%"/',
            function ($matches) {
                $width = intval($matches[1]);
                if ($width > 100) {
                    return 'width="99999' . $width . '"';
                } else {
                    return $matches[0]; // No change needed
                }
            },
            $html
        );

        $cleanhtml = $purifier->purify($processedHtml);

        //Revert the width to the original percentage
        $cleanhtml = preg_replace_callback(
            '/width="99999(\d+)"/',
            function ($matches) {
                $width = intval($matches[1]);
                if ($width > 100) {
                    return 'width="' . $width . '%"';
                } else {
                    return $matches[0]; // No change needed
                }
            },
            $cleanhtml
        );
        return $cleanhtml;
    }

    /**
     * Sanitizes provided HTML by removing images that contain forbidden src to avoid data exfiltration.
     *
     * @param string $html
     * @return string
     */
    public static function removeEmbeddedFilesLinks($html = '')
    {
        if (!is_string($html)) {
            return $html;
        }

        return preg_replace_callback(
            '/(?:<|&lt;|\\\u003C)img(.*?)(?:>|&gt;|\\\u003E)/is',
            function ($matches) {
                $pattern = '/\/EmbeddedFiles/i';
                return preg_match($pattern, $matches[0]) ? '<img src="" alt="removed for security" />' : $matches[0];
            },
            $html
        );
    }
}

/**
 * URI filter for HTMLPurifier
 * Approves only resource URIs that are in the list of trusted domains
 * Until we have comprehensive CSRF protection, we need to sanitize URLs in emails, etc.
 * to avoid CSRF attacks.
 */
class SugarURIFilter extends HTMLPurifier_URIFilter
{
    public $name = 'SugarURIFilter';
    protected $allowed = [];

    public function prepare($config)
    {
        global $sugar_config;
        if (!empty($sugar_config['security_trusted_domains']) && is_array($sugar_config['security_trusted_domains'])) {
            $this->allowed = $sugar_config['security_trusted_domains'];
        }
    }

    public function filter(&$uri, $config, $context)
    {
        // skip non-resource URIs
        if (!$context->get('EmbeddedURI', true)) {
            return true;
        }

        if (!empty($uri->scheme) && strtolower($uri->scheme) != 'http' && strtolower($uri->scheme) != 'https') {
            // do not touch non-HTTP URLs
            return true;
        }

        // allow URLs with no query
        if (empty($uri->query)) {
            return true;
        }

        // allow URLs for known good hosts
        foreach ($this->allowed as $allow) {
            // must be equal to our domain or subdomain of our domain
            if ($uri->host == $allow || substr($uri->host, -(strlen($allow) + 1)) == ".$allow") {
                return true;
            }
        }

        // Here we try to block URLs that may be used for nasty XSRF stuff by
        // referring back to Sugar URLs
        // allow URLs that don't start with /? or /index.php?
        if (!empty($uri->path) && $uri->path != '/') {
            $lpath = strtolower($uri->path);
            if (substr($lpath, -10) != '/index.php' && $lpath != 'index.php') {
                return true;
            }
        }

        $query_items = [];
        parse_str(from_html($uri->query), $query_items);
        // weird query, probably harmless
        if (empty($query_items)) {
            return true;
        }
        // suspiciously like SugarCRM query, reject
        if (!empty($query_items['module']) && !empty($query_items['action'])) {
            return false;
        }
        // looks like non-download entry point - allow only specific entry points
        if (!empty($query_items['entryPoint']) && !in_array($query_items['entryPoint'], ['download', 'image', 'getImage'])) {
            return false;
        }

        return true;
    }
}
