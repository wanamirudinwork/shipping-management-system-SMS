<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\Type\Type;
final class EnumUnresolvedPropertyPrototypeReflection implements UnresolvedPropertyPrototypeReflection
{
    /**
     * @var EnumPropertyReflection
     */
    private $property;
    public function __construct(\PHPStan\Reflection\Php\EnumPropertyReflection $property)
    {
        $this->property = $property;
    }
    public function doNotResolveTemplateTypeMapToBounds() : UnresolvedPropertyPrototypeReflection
    {
        return $this;
    }
    public function getNakedProperty() : ExtendedPropertyReflection
    {
        return $this->property;
    }
    public function getTransformedProperty() : ExtendedPropertyReflection
    {
        return $this->property;
    }
    public function withFechedOnType(Type $type) : UnresolvedPropertyPrototypeReflection
    {
        return $this;
    }
}
