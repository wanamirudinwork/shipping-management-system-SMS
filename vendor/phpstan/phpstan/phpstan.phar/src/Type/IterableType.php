<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Php\PhpVersion;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateMixedType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\Traits\MaybeArrayTypeTrait;
use PHPStan\Type\Traits\MaybeCallableTypeTrait;
use PHPStan\Type\Traits\MaybeObjectTypeTrait;
use PHPStan\Type\Traits\MaybeOffsetAccessibleTypeTrait;
use PHPStan\Type\Traits\NonGeneralizableTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;
use PHPStan\Type\Traits\UndecidedComparisonCompoundTypeTrait;
use Traversable;
use function array_merge;
use function sprintf;
/** @api */
class IterableType implements \PHPStan\Type\CompoundType
{
    /**
     * @var Type
     */
    private $keyType;
    /**
     * @var Type
     */
    private $itemType;
    use MaybeArrayTypeTrait;
    use MaybeCallableTypeTrait;
    use MaybeObjectTypeTrait;
    use MaybeOffsetAccessibleTypeTrait;
    use UndecidedBooleanTypeTrait;
    use UndecidedComparisonCompoundTypeTrait;
    use NonGeneralizableTypeTrait;
    /** @api */
    public function __construct(\PHPStan\Type\Type $keyType, \PHPStan\Type\Type $itemType)
    {
        $this->keyType = $keyType;
        $this->itemType = $itemType;
    }
    public function getKeyType() : \PHPStan\Type\Type
    {
        return $this->keyType;
    }
    public function getItemType() : \PHPStan\Type\Type
    {
        return $this->itemType;
    }
    /**
     * @return string[]
     */
    public function getReferencedClasses() : array
    {
        return array_merge($this->keyType->getReferencedClasses(), $this->getItemType()->getReferencedClasses());
    }
    public function getObjectClassNames() : array
    {
        return [];
    }
    public function getObjectClassReflections() : array
    {
        return [];
    }
    public function getConstantStrings() : array
    {
        return [];
    }
    public function accepts(\PHPStan\Type\Type $type, bool $strictTypes) : TrinaryLogic
    {
        return $this->acceptsWithReason($type, $strictTypes)->result;
    }
    public function acceptsWithReason(\PHPStan\Type\Type $type, bool $strictTypes) : \PHPStan\Type\AcceptsResult
    {
        if ($type->isConstantArray()->yes() && $type->isIterableAtLeastOnce()->no()) {
            return \PHPStan\Type\AcceptsResult::createYes();
        }
        if ($type->isIterable()->yes()) {
            return $this->getIterableValueType()->acceptsWithReason($type->getIterableValueType(), $strictTypes)->and($this->getIterableKeyType()->acceptsWithReason($type->getIterableKeyType(), $strictTypes));
        }
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return $type->isAcceptedWithReasonBy($this, $strictTypes);
        }
        return \PHPStan\Type\AcceptsResult::createNo();
    }
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : TrinaryLogic
    {
        return $this->isSuperTypeOfWithReason($type)->result;
    }
    public function isSuperTypeOfWithReason(\PHPStan\Type\Type $type) : \PHPStan\Type\IsSuperTypeOfResult
    {
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return $type->isSubTypeOfWithReason($this);
        }
        return (new \PHPStan\Type\IsSuperTypeOfResult($type->isIterable(), []))->and($this->getIterableValueType()->isSuperTypeOfWithReason($type->getIterableValueType()))->and($this->getIterableKeyType()->isSuperTypeOfWithReason($type->getIterableKeyType()));
    }
    public function isSuperTypeOfMixed(\PHPStan\Type\Type $type) : TrinaryLogic
    {
        return $type->isIterable()->and($this->isNestedTypeSuperTypeOf($this->getIterableValueType(), $type->getIterableValueType()))->and($this->isNestedTypeSuperTypeOf($this->getIterableKeyType(), $type->getIterableKeyType()));
    }
    private function isNestedTypeSuperTypeOf(\PHPStan\Type\Type $a, \PHPStan\Type\Type $b) : TrinaryLogic
    {
        if (!$a instanceof \PHPStan\Type\MixedType || !$b instanceof \PHPStan\Type\MixedType) {
            return $a->isSuperTypeOf($b);
        }
        if ($a instanceof TemplateMixedType || $b instanceof TemplateMixedType) {
            return $a->isSuperTypeOf($b);
        }
        if ($a->isExplicitMixed()) {
            if ($b->isExplicitMixed()) {
                return TrinaryLogic::createYes();
            }
            return TrinaryLogic::createMaybe();
        }
        return TrinaryLogic::createYes();
    }
    public function isSubTypeOf(\PHPStan\Type\Type $otherType) : TrinaryLogic
    {
        return $this->isSubTypeOfWithReason($otherType)->result;
    }
    public function isSubTypeOfWithReason(\PHPStan\Type\Type $otherType) : \PHPStan\Type\IsSuperTypeOfResult
    {
        if ($otherType instanceof \PHPStan\Type\IntersectionType || $otherType instanceof \PHPStan\Type\UnionType) {
            return $otherType->isSuperTypeOfWithReason(new \PHPStan\Type\UnionType([new \PHPStan\Type\ArrayType($this->keyType, $this->itemType), new \PHPStan\Type\IntersectionType([new \PHPStan\Type\ObjectType(Traversable::class), $this])]));
        }
        if ($otherType instanceof self) {
            $limit = \PHPStan\Type\IsSuperTypeOfResult::createYes();
        } else {
            $limit = \PHPStan\Type\IsSuperTypeOfResult::createMaybe();
        }
        if ($otherType->isConstantArray()->yes() && $otherType->isIterableAtLeastOnce()->no()) {
            return \PHPStan\Type\IsSuperTypeOfResult::createMaybe();
        }
        return $limit->and(new \PHPStan\Type\IsSuperTypeOfResult($otherType->isIterable(), []), $otherType->getIterableValueType()->isSuperTypeOfWithReason($this->itemType), $otherType->getIterableKeyType()->isSuperTypeOfWithReason($this->keyType));
    }
    public function isAcceptedBy(\PHPStan\Type\Type $acceptingType, bool $strictTypes) : TrinaryLogic
    {
        return $this->isAcceptedWithReasonBy($acceptingType, $strictTypes)->result;
    }
    public function isAcceptedWithReasonBy(\PHPStan\Type\Type $acceptingType, bool $strictTypes) : \PHPStan\Type\AcceptsResult
    {
        return $this->isSubTypeOfWithReason($acceptingType)->toAcceptsResult();
    }
    public function equals(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof self) {
            return \false;
        }
        return $this->keyType->equals($type->keyType) && $this->itemType->equals($type->itemType);
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        $isMixedKeyType = $this->keyType instanceof \PHPStan\Type\MixedType && $this->keyType->describe(\PHPStan\Type\VerbosityLevel::precise()) === 'mixed';
        $isMixedItemType = $this->itemType instanceof \PHPStan\Type\MixedType && $this->itemType->describe(\PHPStan\Type\VerbosityLevel::precise()) === 'mixed';
        if ($isMixedKeyType) {
            if ($isMixedItemType) {
                return 'iterable';
            }
            return sprintf('iterable<%s>', $this->itemType->describe($level));
        }
        return sprintf('iterable<%s, %s>', $this->keyType->describe($level), $this->itemType->describe($level));
    }
    public function hasOffsetValueType(\PHPStan\Type\Type $offsetType) : TrinaryLogic
    {
        if ($this->getIterableKeyType()->isSuperTypeOf($offsetType)->no()) {
            return TrinaryLogic::createNo();
        }
        return TrinaryLogic::createMaybe();
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
        return new \PHPStan\Type\ArrayType($this->keyType, $this->getItemType());
    }
    public function toArrayKey() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function isOffsetAccessLegal() : TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }
    public function isIterable() : TrinaryLogic
    {
        return TrinaryLogic::createYes();
    }
    public function isIterableAtLeastOnce() : TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }
    public function getArraySize() : \PHPStan\Type\Type
    {
        return \PHPStan\Type\IntegerRangeType::fromInterval(0, null);
    }
    public function getIterableKeyType() : \PHPStan\Type\Type
    {
        return $this->keyType;
    }
    public function getFirstIterableKeyType() : \PHPStan\Type\Type
    {
        return $this->keyType;
    }
    public function getLastIterableKeyType() : \PHPStan\Type\Type
    {
        return $this->keyType;
    }
    public function getIterableValueType() : \PHPStan\Type\Type
    {
        return $this->getItemType();
    }
    public function getFirstIterableValueType() : \PHPStan\Type\Type
    {
        return $this->getItemType();
    }
    public function getLastIterableValueType() : \PHPStan\Type\Type
    {
        return $this->getItemType();
    }
    public function isNull() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isConstantValue() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isConstantScalarValue() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function getConstantScalarTypes() : array
    {
        return [];
    }
    public function getConstantScalarValues() : array
    {
        return [];
    }
    public function isTrue() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isFalse() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isBoolean() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isFloat() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isInteger() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isString() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isNumericString() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isNonEmptyString() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isNonFalsyString() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isLiteralString() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isLowercaseString() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isUppercaseString() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function isClassStringType() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function getClassStringObjectType() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ErrorType();
    }
    public function getObjectTypeOrClassStringObjectType() : \PHPStan\Type\Type
    {
        return new \PHPStan\Type\ObjectWithoutClassType();
    }
    public function isVoid() : TrinaryLogic
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
    public function inferTemplateTypes(\PHPStan\Type\Type $receivedType) : TemplateTypeMap
    {
        if ($receivedType instanceof \PHPStan\Type\UnionType || $receivedType instanceof \PHPStan\Type\IntersectionType) {
            return $receivedType->inferTemplateTypesOn($this);
        }
        if (!$receivedType->isIterable()->yes()) {
            return TemplateTypeMap::createEmpty();
        }
        $keyTypeMap = $this->getIterableKeyType()->inferTemplateTypes($receivedType->getIterableKeyType());
        $valueTypeMap = $this->getIterableValueType()->inferTemplateTypes($receivedType->getIterableValueType());
        return $keyTypeMap->union($valueTypeMap);
    }
    public function getReferencedTemplateTypes(TemplateTypeVariance $positionVariance) : array
    {
        $variance = $positionVariance->compose(TemplateTypeVariance::createCovariant());
        return array_merge($this->getIterableKeyType()->getReferencedTemplateTypes($variance), $this->getIterableValueType()->getReferencedTemplateTypes($variance));
    }
    public function traverse(callable $cb) : \PHPStan\Type\Type
    {
        $keyType = $cb($this->keyType);
        $itemType = $cb($this->itemType);
        if ($keyType !== $this->keyType || $itemType !== $this->itemType) {
            return new self($keyType, $itemType);
        }
        return $this;
    }
    public function traverseSimultaneously(\PHPStan\Type\Type $right, callable $cb) : \PHPStan\Type\Type
    {
        $keyType = $cb($this->keyType, $right->getIterableKeyType());
        $itemType = $cb($this->itemType, $right->getIterableValueType());
        if ($keyType !== $this->keyType || $itemType !== $this->itemType) {
            return new self($keyType, $itemType);
        }
        return $this;
    }
    public function tryRemove(\PHPStan\Type\Type $typeToRemove) : ?\PHPStan\Type\Type
    {
        $arrayType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        if ($typeToRemove->isSuperTypeOf($arrayType)->yes()) {
            return new GenericObjectType(Traversable::class, [$this->getIterableKeyType(), $this->getIterableValueType()]);
        }
        $traversableType = new \PHPStan\Type\ObjectType(Traversable::class);
        if ($typeToRemove->isSuperTypeOf($traversableType)->yes()) {
            return new \PHPStan\Type\ArrayType($this->getIterableKeyType(), $this->getIterableValueType());
        }
        return null;
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
        $isMixedKeyType = $this->keyType instanceof \PHPStan\Type\MixedType && $this->keyType->describe(\PHPStan\Type\VerbosityLevel::precise()) === 'mixed';
        $isMixedItemType = $this->itemType instanceof \PHPStan\Type\MixedType && $this->itemType->describe(\PHPStan\Type\VerbosityLevel::precise()) === 'mixed';
        if ($isMixedKeyType) {
            if ($isMixedItemType) {
                return new IdentifierTypeNode('iterable');
            }
            return new GenericTypeNode(new IdentifierTypeNode('iterable'), [$this->itemType->toPhpDocNode()]);
        }
        return new GenericTypeNode(new IdentifierTypeNode('iterable'), [$this->keyType->toPhpDocNode(), $this->itemType->toPhpDocNode()]);
    }
    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties) : \PHPStan\Type\Type
    {
        return new self($properties['keyType'], $properties['itemType']);
    }
}
