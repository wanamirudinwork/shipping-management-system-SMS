<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
interface InternalScopeFactory
{
    /**
     * @param array<string, ExpressionTypeHolder> $expressionTypes
     * @param array<string, ExpressionTypeHolder> $nativeExpressionTypes
     * @param array<string, ConditionalExpressionHolder[]> $conditionalExpressions
     * @param list<string> $inClosureBindScopeClasses
     * @param array<string, true> $currentlyAssignedExpressions
     * @param array<string, true> $currentlyAllowedUndefinedExpressions
     * @param list<array{MethodReflection|FunctionReflection|null, ParameterReflection|null}> $inFunctionCallsStack
     * @param FunctionReflection|MethodReflection|null $function
     */
    public function create(\PHPStan\Analyser\ScopeContext $context, bool $declareStrictTypes = \false, $function = null, ?string $namespace = null, array $expressionTypes = [], array $nativeExpressionTypes = [], array $conditionalExpressions = [], array $inClosureBindScopeClasses = [], ?ParametersAcceptor $anonymousFunctionReflection = null, bool $inFirstLevelStatement = \true, array $currentlyAssignedExpressions = [], array $currentlyAllowedUndefinedExpressions = [], array $inFunctionCallsStack = [], bool $afterExtractCall = \false, ?\PHPStan\Analyser\Scope $parentScope = null, bool $nativeTypesPromoted = \false) : \PHPStan\Analyser\MutatingScope;
}
