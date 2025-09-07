<?php

declare (strict_types=1);
namespace PHPStan\Rules\RuleErrors;

use PHPStan\Rules\MetadataRuleError;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\TipRuleError;
/**
 * @internal Use PHPStan\Rules\RuleErrorBuilder instead.
 */
final class RuleError41 implements RuleError, TipRuleError, MetadataRuleError
{
    /**
     * @var string
     */
    public $message;
    /**
     * @var string
     */
    public $tip;
    /** @var mixed[] */
    public $metadata;
    public function getMessage() : string
    {
        return $this->message;
    }
    public function getTip() : string
    {
        return $this->tip;
    }
    /**
     * @return mixed[]
     */
    public function getMetadata() : array
    {
        return $this->metadata;
    }
}
