<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Native;

use PHPStan\Reflection\ParameterReflectionWithPhpDocs;
use PHPStan\Reflection\PassedByReference;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
final class NativeParameterWithPhpDocsReflection implements ParameterReflectionWithPhpDocs
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
    private $type;
    /**
     * @var Type
     */
    private $phpDocType;
    /**
     * @var Type
     */
    private $nativeType;
    /**
     * @var PassedByReference
     */
    private $passedByReference;
    /**
     * @var bool
     */
    private $variadic;
    /**
     * @var ?Type
     */
    private $defaultValue;
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
    public function __construct(string $name, bool $optional, Type $type, Type $phpDocType, Type $nativeType, PassedByReference $passedByReference, bool $variadic, ?Type $defaultValue, ?Type $outType, TrinaryLogic $immediatelyInvokedCallable, ?Type $closureThisType)
    {
        $this->name = $name;
        $this->optional = $optional;
        $this->type = $type;
        $this->phpDocType = $phpDocType;
        $this->nativeType = $nativeType;
        $this->passedByReference = $passedByReference;
        $this->variadic = $variadic;
        $this->defaultValue = $defaultValue;
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
        return $this->type;
    }
    public function getPhpDocType() : Type
    {
        return $this->phpDocType;
    }
    public function getNativeType() : Type
    {
        return $this->nativeType;
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
    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties) : self
    {
        return new self($properties['name'], $properties['optional'], $properties['type'], $properties['phpDocType'], $properties['nativeType'], $properties['passedByReference'], $properties['variadic'], $properties['defaultValue'], $properties['outType'], $properties['immediatelyInvokedCallable'], $properties['closureThisType']);
    }
}
