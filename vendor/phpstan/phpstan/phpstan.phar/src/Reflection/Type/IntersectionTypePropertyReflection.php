<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function array_map;
use function count;
use function implode;
final class IntersectionTypePropertyReflection implements ExtendedPropertyReflection
{
    /**
     * @var PropertyReflection[]
     */
    private $properties;
    /**
     * @param PropertyReflection[] $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }
    public function getDeclaringClass() : ClassReflection
    {
        return $this->properties[0]->getDeclaringClass();
    }
    public function isStatic() : bool
    {
        return $this->computeResult(static function (PropertyReflection $property) {
            return $property->isStatic();
        });
    }
    public function isPrivate() : bool
    {
        return $this->computeResult(static function (PropertyReflection $property) {
            return $property->isPrivate();
        });
    }
    public function isPublic() : bool
    {
        return $this->computeResult(static function (PropertyReflection $property) {
            return $property->isPublic();
        });
    }
    public function isDeprecated() : TrinaryLogic
    {
        return TrinaryLogic::lazyMaxMin($this->properties, static function (PropertyReflection $propertyReflection) : TrinaryLogic {
            return $propertyReflection->isDeprecated();
        });
    }
    public function getDeprecatedDescription() : ?string
    {
        $descriptions = [];
        foreach ($this->properties as $property) {
            if (!$property->isDeprecated()->yes()) {
                continue;
            }
            $description = $property->getDeprecatedDescription();
            if ($description === null) {
                continue;
            }
            $descriptions[] = $description;
        }
        if (count($descriptions) === 0) {
            return null;
        }
        return implode(' ', $descriptions);
    }
    public function isInternal() : TrinaryLogic
    {
        return TrinaryLogic::lazyMaxMin($this->properties, static function (PropertyReflection $propertyReflection) : TrinaryLogic {
            return $propertyReflection->isInternal();
        });
    }
    public function getDocComment() : ?string
    {
        return null;
    }
    public function getReadableType() : Type
    {
        return TypeCombinator::intersect(...array_map(static function (PropertyReflection $property) : Type {
            return $property->getReadableType();
        }, $this->properties));
    }
    public function getWritableType() : Type
    {
        return TypeCombinator::intersect(...array_map(static function (PropertyReflection $property) : Type {
            return $property->getWritableType();
        }, $this->properties));
    }
    public function canChangeTypeAfterAssignment() : bool
    {
        return $this->computeResult(static function (PropertyReflection $property) {
            return $property->canChangeTypeAfterAssignment();
        });
    }
    public function isReadable() : bool
    {
        return $this->computeResult(static function (PropertyReflection $property) {
            return $property->isReadable();
        });
    }
    public function isWritable() : bool
    {
        return $this->computeResult(static function (PropertyReflection $property) {
            return $property->isWritable();
        });
    }
    /**
     * @param callable(PropertyReflection): bool $cb
     */
    private function computeResult(callable $cb) : bool
    {
        $result = \false;
        foreach ($this->properties as $property) {
            $result = $result || $cb($property);
        }
        return $result;
    }
}
