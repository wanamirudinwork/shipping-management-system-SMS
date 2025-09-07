<?php

declare (strict_types=1);
namespace PHPStan\Rules;

use PHPStan\Type\Type;
/**
 * @api
 * @final
 */
class FoundTypeResult
{
    /**
     * @var Type
     */
    private $type;
    /**
     * @var string[]
     */
    private $referencedClasses;
    /**
     * @var list<IdentifierRuleError>
     */
    private $unknownClassErrors;
    /**
     * @var ?string
     */
    private $tip;
    /**
     * @param string[] $referencedClasses
     * @param list<IdentifierRuleError> $unknownClassErrors
     */
    public function __construct(Type $type, array $referencedClasses, array $unknownClassErrors, ?string $tip)
    {
        $this->type = $type;
        $this->referencedClasses = $referencedClasses;
        $this->unknownClassErrors = $unknownClassErrors;
        $this->tip = $tip;
    }
    public function getType() : Type
    {
        return $this->type;
    }
    /**
     * @return string[]
     */
    public function getReferencedClasses() : array
    {
        return $this->referencedClasses;
    }
    /**
     * @return list<IdentifierRuleError>
     */
    public function getUnknownClassErrors() : array
    {
        return $this->unknownClassErrors;
    }
    public function getTip() : ?string
    {
        return $this->tip;
    }
}
