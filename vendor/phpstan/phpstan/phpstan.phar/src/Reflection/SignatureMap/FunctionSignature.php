<?php

declare (strict_types=1);
namespace PHPStan\Reflection\SignatureMap;

use PHPStan\Type\Type;
final class FunctionSignature
{
    /**
     * @var array<int, ParameterSignature>
     */
    private $parameters;
    /**
     * @var Type
     */
    private $returnType;
    /**
     * @var Type
     */
    private $nativeReturnType;
    /**
     * @var bool
     */
    private $variadic;
    /**
     * @param array<int, ParameterSignature> $parameters
     */
    public function __construct(array $parameters, Type $returnType, Type $nativeReturnType, bool $variadic)
    {
        $this->parameters = $parameters;
        $this->returnType = $returnType;
        $this->nativeReturnType = $nativeReturnType;
        $this->variadic = $variadic;
    }
    /**
     * @return array<int, ParameterSignature>
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
    public function getReturnType() : Type
    {
        return $this->returnType;
    }
    public function getNativeReturnType() : Type
    {
        return $this->nativeReturnType;
    }
    public function isVariadic() : bool
    {
        return $this->variadic;
    }
}
