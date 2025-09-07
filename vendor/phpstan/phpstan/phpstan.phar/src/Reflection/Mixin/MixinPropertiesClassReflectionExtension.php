<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Mixin;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\VerbosityLevel;
use function array_intersect;
use function count;
final class MixinPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    /**
     * @var string[]
     */
    private $mixinExcludeClasses;
    /** @var array<string, array<string, true>> */
    private $inProcess = [];
    /**
     * @param string[] $mixinExcludeClasses
     */
    public function __construct(array $mixinExcludeClasses)
    {
        $this->mixinExcludeClasses = $mixinExcludeClasses;
    }
    public function hasProperty(ClassReflection $classReflection, string $propertyName) : bool
    {
        return $this->findProperty($classReflection, $propertyName) !== null;
    }
    public function getProperty(ClassReflection $classReflection, string $propertyName) : PropertyReflection
    {
        $property = $this->findProperty($classReflection, $propertyName);
        if ($property === null) {
            throw new ShouldNotHappenException();
        }
        return $property;
    }
    private function findProperty(ClassReflection $classReflection, string $propertyName) : ?PropertyReflection
    {
        $mixinTypes = $classReflection->getResolvedMixinTypes();
        foreach ($mixinTypes as $type) {
            if (count(array_intersect($type->getObjectClassNames(), $this->mixinExcludeClasses)) > 0) {
                continue;
            }
            $typeDescription = $type->describe(VerbosityLevel::typeOnly());
            if (isset($this->inProcess[$typeDescription][$propertyName])) {
                continue;
            }
            $this->inProcess[$typeDescription][$propertyName] = \true;
            if (!$type->hasProperty($propertyName)->yes()) {
                unset($this->inProcess[$typeDescription][$propertyName]);
                continue;
            }
            $property = $type->getProperty($propertyName, new OutOfClassScope());
            unset($this->inProcess[$typeDescription][$propertyName]);
            return $property;
        }
        foreach ($classReflection->getTraits() as $traitClass) {
            $methodWithDeclaringClass = $this->findProperty($traitClass, $propertyName);
            if ($methodWithDeclaringClass === null) {
                continue;
            }
            return $methodWithDeclaringClass;
        }
        $parentClass = $classReflection->getParentClass();
        while ($parentClass !== null) {
            $property = $this->findProperty($parentClass, $propertyName);
            if ($property !== null) {
                return $property;
            }
            $parentClass = $parentClass->getParentClass();
        }
        return null;
    }
}
