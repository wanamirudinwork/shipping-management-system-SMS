<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function array_map;
use function count;
final class ArrayFindFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    /**
     * @var ArrayFilterFunctionReturnTypeHelper
     */
    private $arrayFilterFunctionReturnTypeHelper;
    public function __construct(\PHPStan\Type\Php\ArrayFilterFunctionReturnTypeHelper $arrayFilterFunctionReturnTypeHelper)
    {
        $this->arrayFilterFunctionReturnTypeHelper = $arrayFilterFunctionReturnTypeHelper;
    }
    public function isFunctionSupported(FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'array_find';
    }
    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope) : ?Type
    {
        if (count($functionCall->getArgs()) < 2) {
            return null;
        }
        $arrayType = $scope->getType($functionCall->getArgs()[0]->value);
        if (count($arrayType->getArrays()) < 1) {
            return null;
        }
        $arrayArg = $functionCall->getArgs()[0]->value ?? null;
        $callbackArg = $functionCall->getArgs()[1]->value ?? null;
        $resultTypes = $this->arrayFilterFunctionReturnTypeHelper->getType($scope, $arrayArg, $callbackArg, null);
        $resultType = TypeCombinator::union(...array_map(static function ($type) {
            return $type->getIterableValueType();
        }, $resultTypes->getArrays()));
        return $resultTypes->isIterableAtLeastOnce()->yes() ? $resultType : TypeCombinator::addNull($resultType);
    }
}
