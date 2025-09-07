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
namespace XTemplate;
use Sugarcrm\Sugarcrm\Security\Escaper\Escape;

class TemplateVariable
{
    private string $value;

    public function __construct($value)
    {
        $this->value = $value instanceof TemplateVariable? $value : $this->htmlDecode((string) $value);
    }

    public function escapeHtml()
    {
        $this->value = Escape::html($this->value);
        return $this;
    }

    public function escapeJs()
    {
        $this->value = Escape::js($this->value);
        return $this;
    }

    public function escapeHtmlAttr()
    {
        $this->value = Escape::htmlAttr($this->value);
        return $this;
    }

    public function escapeUrl()
    {
        $this->value = Escape::url($this->value);
        return $this;
    }

    public function escapeCss()
    {
        $this->value = Escape::css($this->value);
        return $this;
    }

    public function sanitizeHtml()
    {
        $this->value = \SugarCleaner::cleanHtml($this->value);
        return $this;
    }

    public function __toString()
    {
        return $this->value;
    }

    private function htmlDecode($data): string
    {
        // take care of multiple html encodings by repeatedly decoding till there is nothing to decode
        do {
            $oldData = $data;
            $data = htmlspecialchars_decode($data, ENT_QUOTES);
        } while ($data !== $oldData);
        return $data;
    }
}
