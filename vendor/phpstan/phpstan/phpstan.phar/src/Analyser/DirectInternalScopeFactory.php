<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider;
use PHPStan\DependencyInjection\Type\ExpressionTypeResolverExtensionRegistryProvider;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Parser\Parser;
use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Properties\PropertyReflectionFinder;
use PHPStan\ShouldNotHappenException;
use function is_a;
final class DirectInternalScopeFactory implements \PHPStan\Analyser\InternalScopeFactory
{
    /**
     * @var class-string
     */
    private $scopeClass;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var InitializerExprTypeResolver
     */
    private $initializerExprTypeResolver;
    /**
     * @var DynamicReturnTypeExtensionRegistryProvider
     */
    private $dynamicReturnTypeExtensionRegistryProvider;
    /**
     * @var ExpressionTypeResolverExtensionRegistryProvider
     */
    private $expressionTypeResolverExtensionRegistryProvider;
    /**
     * @var ExprPrinter
     */
    private $exprPrinter;
    /**
     * @var TypeSpecifier
     */
    private $typeSpecifier;
    /**
     * @var PropertyReflectionFinder
     */
    private $propertyReflectionFinder;
    /**
     * @var Parser
     */
    private $parser;
    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;
    /**
     * @var RicherScopeGetTypeHelper
     */
    private $richerScopeGetTypeHelper;
    /**
     * @var PhpVersion
     */
    private $phpVersion;
    /**
     * @var bool
     */
    private $explicitMixedInUnknownGenericNew;
    /**
     * @var bool
     */
    private $explicitMixedForGlobalVariables;
    /**
     * @var ConstantResolver
     */
    private $constantResolver;
    /**
     * @param class-string $scopeClass
     */
    public function __construct(string $scopeClass, ReflectionProvider $reflectionProvider, InitializerExprTypeResolver $initializerExprTypeResolver, DynamicReturnTypeExtensionRegistryProvider $dynamicReturnTypeExtensionRegistryProvider, ExpressionTypeResolverExtensionRegistryProvider $expressionTypeResolverExtensionRegistryProvider, ExprPrinter $exprPrinter, \PHPStan\Analyser\TypeSpecifier $typeSpecifier, PropertyReflectionFinder $propertyReflectionFinder, Parser $parser, \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver, \PHPStan\Analyser\RicherScopeGetTypeHelper $richerScopeGetTypeHelper, PhpVersion $phpVersion, bool $explicitMixedInUnknownGenericNew, bool $explicitMixedForGlobalVariables, \PHPStan\Analyser\ConstantResolver $constantResolver)
    {
        $this->scopeClass = $scopeClass;
        $this->reflectionProvider = $reflectionProvider;
        $this->initializerExprTypeResolver = $initializerExprTypeResolver;
        $this->dynamicReturnTypeExtensionRegistryProvider = $dynamicReturnTypeExtensionRegistryProvider;
        $this->expressionTypeResolverExtensionRegistryProvider = $expressionTypeResolverExtensionRegistryProvider;
        $this->exprPrinter = $exprPrinter;
        $this->typeSpecifier = $typeSpecifier;
        $this->propertyReflectionFinder = $propertyReflectionFinder;
        $this->parser = $parser;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->richerScopeGetTypeHelper = $richerScopeGetTypeHelper;
        $this->phpVersion = $phpVersion;
        $this->explicitMixedInUnknownGenericNew = $explicitMixedInUnknownGenericNew;
        $this->explicitMixedForGlobalVariables = $explicitMixedForGlobalVariables;
        $this->constantResolver = $constantResolver;
    }
    /**
     * @param array<string, ExpressionTypeHolder> $expressionTypes
     * @param array<string, ExpressionTypeHolder> $nativeExpressionTypes
     * @param array<string, ConditionalExpressionHolder[]> $conditionalExpressions
     * @param list<array{FunctionReflection|MethodReflection|null, ParameterReflection|null}> $inFunctionCallsStack
     * @param array<string, true> $currentlyAssignedExpressions
     * @param array<string, true> $currentlyAllowedUndefinedExpressions
     * @param FunctionReflection|MethodReflection|null $function
     */
    public function create(\PHPStan\Analyser\ScopeContext $context, bool $declareStrictTypes = \false, $function = null, ?string $namespace = null, array $expressionTypes = [], array $nativeExpressionTypes = [], array $conditionalExpressions = [], array $inClosureBindScopeClasses = [], ?ParametersAcceptor $anonymousFunctionReflection = null, bool $inFirstLevelStatement = \true, array $currentlyAssignedExpressions = [], array $currentlyAllowedUndefinedExpressions = [], array $inFunctionCallsStack = [], bool $afterExtractCall = \false, ?\PHPStan\Analyser\Scope $parentScope = null, bool $nativeTypesPromoted = \false) : \PHPStan\Analyser\MutatingScope
    {
        $scopeClass = $this->scopeClass;
        if (!is_a($scopeClass, \PHPStan\Analyser\MutatingScope::class, \true)) {
            throw new ShouldNotHappenException();
        }
        return new $scopeClass($this, $this->reflectionProvider, $this->initializerExprTypeResolver, $this->dynamicReturnTypeExtensionRegistryProvider->getRegistry(), $this->expressionTypeResolverExtensionRegistryProvider->getRegistry(), $this->exprPrinter, $this->typeSpecifier, $this->propertyReflectionFinder, $this->parser, $this->nodeScopeResolver, $this->richerScopeGetTypeHelper, $this->constantResolver, $context, $this->phpVersion, $declareStrictTypes, $function, $namespace, $expressionTypes, $nativeExpressionTypes, $conditionalExpressions, $inClosureBindScopeClasses, $anonymousFunctionReflection, $inFirstLevelStatement, $currentlyAssignedExpressions, $currentlyAllowedUndefinedExpressions, $inFunctionCallsStack, $afterExtractCall, $parentScope, $nativeTypesPromoted, $this->explicitMixedInUnknownGenericNew, $this->explicitMixedForGlobalVariables);
    }
}
