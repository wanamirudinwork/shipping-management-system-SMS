<?php

declare (strict_types=1);
namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\AccessoryLowercaseStringType;
use PHPStan\Type\Accessory\AccessoryUppercaseStringType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeUtils;
use function count;
final class ExplodeFunctionDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    /**
     * @var PhpVersion
     */
    private $phpVersion;
    public function __construct(PhpVersion $phpVersion)
    {
        $this->phpVersion = $phpVersion;
    }
    public function isFunctionSupported(FunctionReflection $functionReflection) : bool
    {
        return $functionReflection->getName() === 'explode';
    }
    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope) : ?Type
    {
        $args = $functionCall->getArgs();
        if (count($args) < 2) {
            return null;
        }
        $delimiterType = $scope->getType($args[0]->value);
        $isEmptyString = (new ConstantStringType(''))->isSuperTypeOf($delimiterType);
        if ($isEmptyString->yes()) {
            if ($this->phpVersion->throwsTypeErrorForInternalFunctions()) {
                return new NeverType();
            }
            return new ConstantBooleanType(\false);
        }
        $stringType = $scope->getType($args[1]->value);
        $accessory = [];
        if ($stringType->isLowercaseString()->yes()) {
            $accessory[] = new AccessoryLowercaseStringType();
        }
        if ($stringType->isUppercaseString()->yes()) {
            $accessory[] = new AccessoryUppercaseStringType();
        }
        if (count($accessory) > 0) {
            $accessory[] = new StringType();
            $returnValueType = new IntersectionType($accessory);
        } else {
            $returnValueType = new StringType();
        }
        $returnType = AccessoryArrayListType::intersectWith(new ArrayType(new IntegerType(), $returnValueType));
        if (!isset($args[2]) || IntegerRangeType::fromInterval(0, null)->isSuperTypeOf($scope->getType($args[2]->value))->yes()) {
            $returnType = TypeCombinator::intersect($returnType, new NonEmptyArrayType());
        }
        if (!$this->phpVersion->throwsValueErrorForInternalFunctions() && $isEmptyString->maybe()) {
            $returnType = TypeCombinator::union($returnType, new ConstantBooleanType(\false));
        }
        if ($delimiterType instanceof MixedType) {
            $returnType = TypeUtils::toBenevolentUnion($returnType);
        }
        return $returnType;
    }
}
