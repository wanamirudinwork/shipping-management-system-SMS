<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Type;
use function array_map;
final class IntersectionTypeUnresolvedPropertyPrototypeReflection implements \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
{
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var UnresolvedPropertyPrototypeReflection[]
     */
    private $propertyPrototypes;
    /**
     * @var ?ExtendedPropertyReflection
     */
    private $transformedProperty = null;
    /**
     * @var ?self
     */
    private $cachedDoNotResolveTemplateTypeMapToBounds = null;
    /**
     * @param UnresolvedPropertyPrototypeReflection[] $propertyPrototypes
     */
    public function __construct(string $propertyName, array $propertyPrototypes)
    {
        $this->propertyName = $propertyName;
        $this->propertyPrototypes = $propertyPrototypes;
    }
    public function doNotResolveTemplateTypeMapToBounds() : \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
    {
        if ($this->cachedDoNotResolveTemplateTypeMapToBounds !== null) {
            return $this->cachedDoNotResolveTemplateTypeMapToBounds;
        }
        return $this->cachedDoNotResolveTemplateTypeMapToBounds = new self($this->propertyName, array_map(static function (\PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection $prototype) : \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection {
            return $prototype->doNotResolveTemplateTypeMapToBounds();
        }, $this->propertyPrototypes));
    }
    public function getNakedProperty() : ExtendedPropertyReflection
    {
        return $this->getTransformedProperty();
    }
    public function getTransformedProperty() : ExtendedPropertyReflection
    {
        if ($this->transformedProperty !== null) {
            return $this->transformedProperty;
        }
        $properties = array_map(static function (\PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection $prototype) : PropertyReflection {
            return $prototype->getTransformedProperty();
        }, $this->propertyPrototypes);
        return $this->transformedProperty = new \PHPStan\Reflection\Type\IntersectionTypePropertyReflection($properties);
    }
    public function withFechedOnType(Type $type) : \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection
    {
        return new self($this->propertyName, array_map(static function (\PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection $prototype) use($type) : \PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection {
            return $prototype->withFechedOnType($type);
        }, $this->propertyPrototypes));
    }
}
