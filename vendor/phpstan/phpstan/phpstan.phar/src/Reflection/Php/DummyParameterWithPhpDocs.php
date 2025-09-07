<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflectionWithPhpDocs;
use PHPStan\Reflection\PassedByReference;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
final class DummyParameterWithPhpDocs extends \PHPStan\Reflection\Php\DummyParameter implements ParameterReflectionWithPhpDocs
{
    /**
     * @var Type
     */
    private $nativeType;
    /**
     * @var Type
     */
    private $phpDocType;
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
    public function __construct(string $name, Type $type, bool $optional, ?PassedByReference $passedByReference, bool $variadic, ?Type $defaultValue, Type $nativeType, Type $phpDocType, ?Type $outType, TrinaryLogic $immediatelyInvokedCallable, ?Type $closureThisType)
    {
        $this->nativeType = $nativeType;
        $this->phpDocType = $phpDocType;
        $this->outType = $outType;
        $this->immediatelyInvokedCallable = $immediatelyInvokedCallable;
        $this->closureThisType = $closureThisType;
        parent::__construct($name, $type, $optional, $passedByReference, $variadic, $defaultValue);
    }
    public function getPhpDocType() : Type
    {
        return $this->phpDocType;
    }
    public function getNativeType() : Type
    {
        return $this->nativeType;
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
