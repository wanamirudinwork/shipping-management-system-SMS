<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflectionWithPhpDocs;
use PHPStan\Reflection\PassedByReference;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;
final class PhpParameterFromParserNodeReflection implements ParameterReflectionWithPhpDocs
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var bool
     */
    private $optional;
    /**
     * @var Type
     */
    private $realType;
    /**
     * @var ?Type
     */
    private $phpDocType;
    /**
     * @var PassedByReference
     */
    private $passedByReference;
    /**
     * @var ?Type
     */
    private $defaultValue;
    /**
     * @var bool
     */
    private $variadic;
    /**
     * @var ?Type
     */
    private $outType;
    /**
     * @var TrinaryLogic
     */
    private $immediatelyInvokedCallable;
    /**
     * @var ?Type
     */
    private $closureThisType;
    /**
     * @var ?Type
     */
    private $type = null;
    public function __construct(string $name, bool $optional, Type $realType, ?Type $phpDocType, PassedByReference $passedByReference, ?Type $defaultValue, bool $variadic, ?Type $outType, TrinaryLogic $immediatelyInvokedCallable, ?Type $closureThisType)
    {
        $this->name = $name;
        $this->optional = $optional;
        $this->realType = $realType;
        $this->phpDocType = $phpDocType;
        $this->passedByReference = $passedByReference;
        $this->defaultValue = $defaultValue;
        $this->variadic = $variadic;
        $this->outType = $outType;
        $this->immediatelyInvokedCallable = $immediatelyInvokedCallable;
        $this->closureThisType = $closureThisType;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function isOptional() : bool
    {
        return $this->optional;
    }
    public function getType() : Type
    {
        if ($this->type === null) {
            $phpDocType = $this->phpDocType;
            if ($phpDocType !== null && $this->defaultValue !== null) {
                if ($this->defaultValue->isNull()->yes()) {
                    $inferred = $phpDocType->inferTemplateTypes($this->defaultValue);
                    if ($inferred->isEmpty()) {
                        $phpDocType = TypeCombinator::addNull($phpDocType);
                    }
                }
            }
            $this->type = TypehintHelper::decideType($this->realType, $phpDocType);
        }
        return $this->type;
    }
    public function getPhpDocType() : Type
    {
        return $this->phpDocType ?? new MixedType();
    }
    public function getNativeType() : Type
    {
        return $this->realType;
    }
    public function passedByReference() : PassedByReference
    {
        return $this->passedByReference;
    }
    public function isVariadic() : bool
    {
        return $this->variadic;
    }
    public function getDefaultValue() : ?Type
    {
        return $this->defaultValue;
    }
    public function getOutType() : ?Type
    {
        return $this->outType;
    }
    public function isImmediatelyInvokedCallable() : TrinaryLogic
    {
        return $this->immediatelyInvokedCallable;
    }
    public function getClosureThisType() : ?Type
    {
        return $this->closureThisType;
    }
}
