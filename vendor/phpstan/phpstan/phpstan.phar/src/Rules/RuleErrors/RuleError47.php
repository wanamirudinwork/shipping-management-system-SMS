<?php

declare (strict_types=1);
namespace PHPStan\Rules\RuleErrors;

use PHPStan\Rules\FileRuleError;
use PHPStan\Rules\LineRuleError;
use PHPStan\Rules\MetadataRuleError;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\TipRuleError;
/**
 * @internal Use PHPStan\Rules\RuleErrorBuilder instead.
 */
final class RuleError47 implements RuleError, LineRuleError, FileRuleError, TipRuleError, MetadataRuleError
{
    /**
     * @var string
     */
    public $message;
    /**
     * @var int
     */
    public $line;
    /**
     * @var string
     */
    public $file;
    /**
     * @var string
     */
    public $fileDescription;
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
    public function getLine() : int
    {
        return $this->line;
    }
    public function getFile() : string
    {
        return $this->file;
    }
    public function getFileDescription() : string
    {
        return $this->fileDescription;
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
