<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\Reflection\InitializerExprContext;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Reflection\ParameterReflectionWithPhpDocs;
use PHPStan\Reflection\PassedByReference;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;
final class PhpParameterReflection implements ParameterReflectionWithPhpDocs
{
    /**
     * @var InitializerExprTypeResolver
     */
    private $initializerExprTypeResolver;
    /**
     * @var ReflectionParameter
     */
    private $reflection;
    /**
     * @var ?Type
     */
    private $phpDocType;
    /**
     * @var ?string
     */
    private $declaringClassName;
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
    /**
     * @var ?Type
     */
    private $nativeType = null;
    public function __construct(InitializerExprTypeResolver $initializerExprTypeResolver, ReflectionParameter $reflection, ?Type $phpDocType, ?string $declaringClassName, ?Type $outType, TrinaryLogic $immediatelyInvokedCallable, ?Type $closureThisType)
    {
        $this->initializerExprTypeResolver = $initializerExprTypeResolver;
        $this->reflection = $reflection;
        $this->phpDocType = $phpDocType;
        $this->declaringClassName = $declaringClassName;
        $this->outType = $outType;
        $this->immediatelyInvokedCallable = $immediatelyInvokedCallable;
        $this->closureThisType = $closureThisType;
    }
    public function isOptional() : bool
    {
        return $this->reflection->isOptional();
    }
    public function getName() : string
    {
        return $this->reflection->getName();
    }
    public function getType() : Type
    {
        if ($this->type === null) {
            $phpDocType = $this->phpDocType;
            if ($phpDocType !== null && $this->reflection->isDefaultValueAvailable()) {
                $defaultValueType = $this->initializerExprTypeResolver->getType($this->reflection->getDefaultValueExpression(), InitializerExprContext::fromReflectionParameter($this->reflection));
                if ($defaultValueType->isNull()->yes()) {
                    $phpDocType = TypeCombinator::addNull($phpDocType);
                }
            }
            $this->type = TypehintHelper::decideTypeFromReflection($this->reflection->getType(), $phpDocType, $this->declaringClassName, $this->isVariadic());
        }
        return $this->type;
    }
    public function passedByReference() : PassedByReference
    {
        return $this->reflection->isPassedByReference() ? PassedByReference::createCreatesNewVariable() : PassedByReference::createNo();
    }
    public function isVariadic() : bool
    {
        return $this->reflection->isVariadic();
    }
    public function getPhpDocType() : Type
    {
        if ($this->phpDocType !== null) {
            return $this->phpDocType;
        }
        return new MixedType();
    }
    public function getNativeType() : Type
    {
        if ($this->nativeType === null) {
            $this->nativeType = TypehintHelper::decideTypeFromReflection($this->reflection->getType(), null, $this->declaringClassName, $this->isVariadic());
        }
        return $this->nativeType;
    }
    public function getDefaultValue() : ?Type
    {
        if ($this->reflection->isDefaultValueAvailable()) {
            return $this->initializerExprTypeResolver->getType($this->reflection->getDefaultValueExpression(), InitializerExprContext::fromReflectionParameter($this->reflection));
        }
        return null;
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
