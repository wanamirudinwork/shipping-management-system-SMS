<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Node\InvalidateExprNode;
use PHPStan\Reflection\Callables\CallableParametersAcceptor;
use PHPStan\Reflection\Callables\SimpleImpurePoint;
use PHPStan\Reflection\Callables\SimpleThrowPoint;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeVarianceMap;
use PHPStan\Type\Type;
final class ResolvedFunctionVariantWithCallable implements \PHPStan\Reflection\ResolvedFunctionVariant, CallableParametersAcceptor
{
    /**
     * @var ResolvedFunctionVariant
     */
    private $parametersAcceptor;
    /**
     * @var SimpleThrowPoint[]
     */
    private $throwPoints;
    /**
     * @var TrinaryLogic
     */
    private $isPure;
    /**
     * @var SimpleImpurePoint[]
     */
    private $impurePoints;
    /**
     * @var InvalidateExprNode[]
     */
    private $invalidateExpressions;
    /**
     * @var string[]
     */
    private $usedVariables;
    /**
     * @var bool
     */
    private $acceptsNamedArguments;
    /**
     * @param SimpleThrowPoint[] $throwPoints
     * @param SimpleImpurePoint[] $impurePoints
     * @param InvalidateExprNode[] $invalidateExpressions
     * @param string[] $usedVariables
     */
    public function __construct(\PHPStan\Reflection\ResolvedFunctionVariant $parametersAcceptor, array $throwPoints, TrinaryLogic $isPure, array $impurePoints, array $invalidateExpressions, array $usedVariables, bool $acceptsNamedArguments)
    {
        $this->parametersAcceptor = $parametersAcceptor;
        $this->throwPoints = $throwPoints;
        $this->isPure = $isPure;
        $this->impurePoints = $impurePoints;
        $this->invalidateExpressions = $invalidateExpressions;
        $this->usedVariables = $usedVariables;
        $this->acceptsNamedArguments = $acceptsNamedArguments;
    }
    public function getOriginalParametersAcceptor() : \PHPStan\Reflection\ParametersAcceptor
    {
        return $this->parametersAcceptor->getOriginalParametersAcceptor();
    }
    public function getTemplateTypeMap() : TemplateTypeMap
    {
        return $this->parametersAcceptor->getTemplateTypeMap();
    }
    public function getResolvedTemplateTypeMap() : TemplateTypeMap
    {
        return $this->parametersAcceptor->getResolvedTemplateTypeMap();
    }
    public function getCallSiteVarianceMap() : TemplateTypeVarianceMap
    {
        return $this->parametersAcceptor->getCallSiteVarianceMap();
    }
    public function getParameters() : array
    {
        return $this->parametersAcceptor->getParameters();
    }
    public function isVariadic() : bool
    {
        return $this->parametersAcceptor->isVariadic();
    }
    public function getReturnTypeWithUnresolvableTemplateTypes() : Type
    {
        return $this->parametersAcceptor->getReturnTypeWithUnresolvableTemplateTypes();
    }
    public function getPhpDocReturnTypeWithUnresolvableTemplateTypes() : Type
    {
        return $this->parametersAcceptor->getPhpDocReturnTypeWithUnresolvableTemplateTypes();
    }
    public function getReturnType() : Type
    {
        return $this->parametersAcceptor->getReturnType();
    }
    public function getPhpDocReturnType() : Type
    {
        return $this->parametersAcceptor->getPhpDocReturnType();
    }
    public function getNativeReturnType() : Type
    {
        return $this->parametersAcceptor->getNativeReturnType();
    }
    public function getThrowPoints() : array
    {
        return $this->throwPoints;
    }
    public function isPure() : TrinaryLogic
    {
        return $this->isPure;
    }
    public function getImpurePoints() : array
    {
        return $this->impurePoints;
    }
    public function getInvalidateExpressions() : array
    {
        return $this->invalidateExpressions;
    }
    public function getUsedVariables() : array
    {
        return $this->usedVariables;
    }
    public function acceptsNamedArguments() : bool
    {
        return $this->acceptsNamedArguments;
    }
}
