<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Php\PhpVersion;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\NonArrayTypeTrait;
use PHPStan\Type\Traits\NonCallableTypeTrait;
use PHPStan\Type\Traits\NonGeneralizableTypeTrait;
use PHPStan\Type\Traits\NonGenericTypeTrait;
use PHPStan\Type\Traits\NonIterableTypeTrait;
use PHPStan\Type\Traits\NonOffsetAccessibleTypeTrait;
use PHPStan\Type\Traits\NonRemoveableTypeTrait;
use PHPStan\Type\Traits\TruthyBooleanTypeTrait;
use PHPStan\Type\Traits\UndecidedComparisonTypeTrait;
class NonexistentParentClassType implements \PHPStan\Type\Type
{
    use \PHPStan\Type\JustNullableTypeTrait;
    use NonArrayTypeTrait;
    use NonCallableTypeTrait;
    use NonIterableTypeTrait;
    use NonOffsetAccessibleTypeTrait;
    use TruthyBooleanTypeTrait;
    use NonGenericTypeTrait;
    use UndecidedComparisonTypeTrait;
    use NonRemoveableTypeTrait;
    use NonGeneralizableTypeTrait;
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        return 'parent';
    }
    public function getTemplateType(string $ancestorClassName, string $templateTypeName) : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function isObject() : TrinaryLogic
    {
        return TrinaryLogic::createYes();
    }
    public function isEnum() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function canAccessProperties() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function hasProperty(string $propertyName) : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope) : PropertyReflection
    {
        throw new ShouldNotHappenException();
    }
    public function getUnresolvedPropertyPrototype(string $propertyName, ClassMemberAccessAnswerer $scope) : UnresolvedPropertyPrototypeReflection
    {
        throw new ShouldNotHappenException();
    }
    public function canCallMethods() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function hasMethod(string $methodName) : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope) : ExtendedMethodReflection
    {
        throw new ShouldNotHappenException();
    }
    public function getUnresolvedMethodPrototype(string $methodName, ClassMemberAccessAnswerer $scope) : UnresolvedMethodPrototypeReflection
    {
        throw new ShouldNotHappenException();
    }
    public function canAccessConstants() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function hasConstant(string $constantName) : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function getConstant(string $constantName) : ConstantReflection
    {
        throw new ShouldNotHappenException();
    }
    public function getConstantStrings() : array
    {
        return [];
    }
    public function isCloneable() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function toNumber() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toAbsoluteNumber() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toString() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toInteger() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toFloat() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toArray() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function toArrayKey() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function isOffsetAccessLegal() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isScalar() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function looseCompare(\PHPStan\Type\Type $type, PhpVersion $phpVersion) : \PHPStan\Type\BooleanType
    {
        return new \PHPStan\Type\BooleanType();
    }
    public function getEnumCases() : array
    {
        return [];
    }
    public function exponentiate(\PHPStan\Type\Type $exponent) : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function getFiniteTypes() : array
    {
        return [];
    }
    public function toPhpDocNode() : TypeNode
    {
        return new IdentifierTypeNode('parent');
    }
    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties) : \PHPStan\Type\Type
    {
        return new self();
    }
}
