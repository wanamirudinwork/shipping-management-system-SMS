<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Mixin;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\VerbosityLevel;
use function array_intersect;
use function count;
final class MixinMethodsClassReflectionExtension implements MethodsClassReflectionExtension
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
    public function hasMethod(ClassReflection $classReflection, string $methodName) : bool
    {
        return $this->findMethod($classReflection, $methodName) !== null;
    }
    public function getMethod(ClassReflection $classReflection, string $methodName) : MethodReflection
    {
        $method = $this->findMethod($classReflection, $methodName);
        if ($method === null) {
            throw new ShouldNotHappenException();
        }
        return $method;
    }
    private function findMethod(ClassReflection $classReflection, string $methodName) : ?MethodReflection
    {
        $mixinTypes = $classReflection->getResolvedMixinTypes();
        foreach ($mixinTypes as $type) {
            if (count(array_intersect($type->getObjectClassNames(), $this->mixinExcludeClasses)) > 0) {
                continue;
            }
            $typeDescription = $type->describe(VerbosityLevel::typeOnly());
            if (isset($this->inProcess[$typeDescription][$methodName])) {
                continue;
            }
            $this->inProcess[$typeDescription][$methodName] = \true;
            if (!$type->hasMethod($methodName)->yes()) {
                unset($this->inProcess[$typeDescription][$methodName]);
                continue;
            }
            $method = $type->getMethod($methodName, new OutOfClassScope());
            unset($this->inProcess[$typeDescription][$methodName]);
            $static = $method->isStatic();
            if (!$static && $classReflection->hasNativeMethod('__callStatic')) {
                $static = \true;
            }
            return new \PHPStan\Reflection\Mixin\MixinMethodReflection($method, $static);
        }
        foreach ($classReflection->getTraits() as $traitClass) {
            $methodWithDeclaringClass = $this->findMethod($traitClass, $methodName);
            if ($methodWithDeclaringClass === null) {
                continue;
            }
            return $methodWithDeclaringClass;
        }
        $parentClass = $classReflection->getParentClass();
        while ($parentClass !== null) {
            $method = $this->findMethod($parentClass, $methodName);
            if ($method !== null) {
                return $method;
            }
            $parentClass = $parentClass->getParentClass();
        }
        return null;
    }
}
