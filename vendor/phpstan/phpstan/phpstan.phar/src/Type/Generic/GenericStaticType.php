<?php

declare (strict_types=1);
namespace PHPStan\Type\Generic;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProviderStaticAccessor;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use function array_key_exists;
use function array_map;
use function count;
/** @api */
class GenericStaticType extends StaticType
{
    /**
     * @var ClassReflection
     */
    private $classReflection;
    /**
     * @var array<int, Type>
     */
    private $types;
    /**
     * @var ?Type
     */
    private $subtractedType;
    /**
     * @var array<int, TemplateTypeVariance>
     */
    private $variances;
    /**
     * @var ?ObjectType
     */
    private $staticObjectType = null;
    /**
     * @var string
     */
    private $baseClass;
    /**
     * @api
     * @param array<int, Type> $types
     * @param array<int, TemplateTypeVariance> $variances
     */
    public function __construct(ClassReflection $classReflection, array $types, ?Type $subtractedType, array $variances)
    {
        $this->classReflection = $classReflection;
        $this->types = $types;
        $this->subtractedType = $subtractedType;
        $this->variances = $variances;
        if (count($this->types) === 0) {
            throw new ShouldNotHappenException('Cannot create GenericStaticType with zero types.');
        }
        parent::__construct($classReflection, $subtractedType);
        $this->baseClass = $classReflection->getName();
    }
    public function getClassName() : string
    {
        return $this->baseClass;
    }
    /**
     * @return array<int, Type>
     */
    public function getTypes() : array
    {
        return $this->types;
    }
    /** @return array<int, TemplateTypeVariance> */
    public function getVariances() : array
    {
        return $this->variances;
    }
    public function getStaticObjectType() : ObjectType
    {
        if ($this->staticObjectType === null) {
            if ($this->classReflection->isGeneric()) {
                return $this->staticObjectType = new \PHPStan\Type\Generic\GenericObjectType($this->classReflection->getName(), $this->types, $this->subtractedType, $this->classReflection, $this->variances);
            }
            return $this->staticObjectType = parent::getStaticObjectType();
        }
        return $this->staticObjectType;
    }
    public function changeBaseClass(ClassReflection $classReflection) : StaticType
    {
        if ($classReflection->getName() === $this->getClassName()) {
            return $this;
        }
        if (!$classReflection->isGeneric()) {
            return new StaticType($classReflection);
        }
        $templateTags = $this->getClassReflection()->getTemplateTags();
        $i = 0;
        $indexedTypes = [];
        $indexedVariances = [];
        foreach ($templateTags as $typeName => $tag) {
            if (!array_key_exists($i, $this->types)) {
                break;
            }
            if (!array_key_exists($i, $this->variances)) {
                break;
            }
            $indexedTypes[$typeName] = $this->types[$i];
            $indexedVariances[$typeName] = $this->variances[$i];
            $i++;
        }
        $newType = new \PHPStan\Type\Generic\GenericObjectType($classReflection->getName(), $classReflection->typeMapToList($classReflection->getTemplateTypeMap()));
        $ancestorType = $newType->getAncestorWithClassName($this->getClassName());
        if ($ancestorType === null) {
            return new self($classReflection, $classReflection->typeMapToList($classReflection->getTemplateTypeMap()->resolveToBounds()), $this->subtractedType, $classReflection->varianceMapToList($classReflection->getCallSiteVarianceMap()));
        }
        $ancestorClassReflection = $ancestorType->getClassReflection();
        if ($ancestorClassReflection === null) {
            return new self($classReflection, $classReflection->typeMapToList($classReflection->getTemplateTypeMap()->resolveToBounds()), $this->subtractedType, $classReflection->varianceMapToList($classReflection->getCallSiteVarianceMap()));
        }
        $newClassTypes = [];
        $newClassVariances = [];
        foreach ($ancestorClassReflection->getActiveTemplateTypeMap()->getTypes() as $typeName => $templateType) {
            if (!$templateType instanceof \PHPStan\Type\Generic\TemplateType) {
                continue;
            }
            if (!array_key_exists($typeName, $indexedTypes)) {
                continue;
            }
            $newClassTypes[$templateType->getName()] = $indexedTypes[$typeName];
            $newClassVariances[$templateType->getName()] = $indexedVariances[$typeName];
        }
        return new self($classReflection, $classReflection->typeMapToList(new \PHPStan\Type\Generic\TemplateTypeMap($newClassTypes)), $this->subtractedType, $classReflection->varianceMapToList(new \PHPStan\Type\Generic\TemplateTypeVarianceMap($newClassVariances)));
    }
    public function isSuperTypeOfWithReason(Type $type) : IsSuperTypeOfResult
    {
        if ($type instanceof CompoundType) {
            return $type->isSubTypeOfWithReason($this);
        }
        if ($type instanceof self) {
            return $this->getStaticObjectType()->isSuperTypeOfWithReason($type->getStaticObjectType());
        }
        return parent::isSuperTypeOfWithReason($type)->and(IsSuperTypeOfResult::createMaybe());
    }
    public function traverse(callable $cb) : Type
    {
        $subtractedType = $this->getSubtractedType() !== null ? $cb($this->getSubtractedType()) : null;
        $typesChanged = \false;
        $types = [];
        foreach ($this->types as $type) {
            $newType = $cb($type);
            $types[] = $newType;
            if ($newType === $type) {
                continue;
            }
            $typesChanged = \true;
        }
        if ($subtractedType !== $this->getSubtractedType() || $typesChanged) {
            return new self($this->classReflection, $types, $subtractedType, $this->variances);
        }
        return $this;
    }
    public function traverseSimultaneously(Type $right, callable $cb) : Type
    {
        if (!$right instanceof TypeWithClassName) {
            return $this;
        }
        $ancestor = $right->getAncestorWithClassName($this->getClassName());
        if (!$ancestor instanceof self) {
            return $this;
        }
        if (count($this->types) !== count($ancestor->types)) {
            return $this;
        }
        $typesChanged = \false;
        $types = [];
        foreach ($this->types as $i => $leftType) {
            $rightType = $ancestor->types[$i];
            $newType = $cb($leftType, $rightType);
            $types[] = $newType;
            if ($newType === $leftType) {
                continue;
            }
            $typesChanged = \true;
        }
        if ($typesChanged) {
            return new self($this->classReflection, $types, null, $this->variances);
        }
        return $this;
    }
    public function changeSubtractedType(?Type $subtractedType) : Type
    {
        if ($subtractedType !== null) {
            $classReflection = $this->getClassReflection();
            if ($classReflection->getAllowedSubTypes() !== null) {
                $objectType = $this->getStaticObjectType()->changeSubtractedType($subtractedType);
                if ($objectType instanceof NeverType) {
                    return $objectType;
                }
                if ($objectType instanceof ObjectType && $objectType->getSubtractedType() !== null) {
                    return new self($classReflection, $this->types, $objectType->getSubtractedType(), $this->variances);
                }
                return TypeCombinator::intersect($this, $objectType);
            }
        }
        return new self($this->classReflection, $this->types, $subtractedType, $this->variances);
    }
    public function inferTemplateTypes(Type $receivedType) : \PHPStan\Type\Generic\TemplateTypeMap
    {
        return $this->getStaticObjectType()->inferTemplateTypes($receivedType);
    }
    public function getReferencedTemplateTypes(\PHPStan\Type\Generic\TemplateTypeVariance $positionVariance) : array
    {
        return $this->getStaticObjectType()->getReferencedTemplateTypes($positionVariance);
    }
    public function toPhpDocNode() : TypeNode
    {
        /** @var IdentifierTypeNode $parent */
        $parent = parent::toPhpDocNode();
        return new GenericTypeNode($parent, array_map(static function (Type $type) {
            return $type->toPhpDocNode();
        }, $this->types), array_map(static function (\PHPStan\Type\Generic\TemplateTypeVariance $variance) {
            return $variance->toPhpDocNodeVariance();
        }, $this->variances));
    }
    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties) : Type
    {
        $reflectionProvider = ReflectionProviderStaticAccessor::getInstance();
        if ($reflectionProvider->hasClass($properties['baseClass'])) {
            return new self($reflectionProvider->getClass($properties['baseClass']), $properties['types'], $properties['subtractedType'], $properties['variances']);
        }
        return new ErrorType();
    }
}
