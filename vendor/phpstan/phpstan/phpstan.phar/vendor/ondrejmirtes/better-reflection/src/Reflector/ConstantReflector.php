<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflector;

use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionConstant;
use PHPStan\BetterReflection\Reflection\ReflectionFunction;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
/**
 * @deprecated Use Roave\BetterReflection\Reflector\Reflector instead.
 */
class ConstantReflector implements \PHPStan\BetterReflection\Reflector\Reflector
{
    /** @var Reflector */
    private $reflector;
    public function __construct(SourceLocator $sourceLocator)
    {
        $this->reflector = new \PHPStan\BetterReflection\Reflector\DefaultReflector($sourceLocator);
    }
    /**
     * Create a ReflectionFunction for the specified $functionName.
     *
     * @throws IdentifierNotFound
     */
    public function reflect(string $constantName) : ReflectionConstant
    {
        return $this->reflector->reflectConstant($constantName);
    }
    /**
     * Get all the classes available in the scope specified by the SourceLocator.
     *
     * @return ReflectionConstant[]
     */
    public function getAllConstants() : array
    {
        return $this->reflector->reflectAllConstants();
    }
    public function reflectClass(string $identifierName) : ReflectionClass
    {
        return $this->reflector->reflectClass($identifierName);
    }
    /**
     * @return list<ReflectionClass>
     */
    public function reflectAllClasses() : iterable
    {
        return $this->reflector->reflectAllClasses();
    }
    public function reflectFunction(string $identifierName) : ReflectionFunction
    {
        return $this->reflector->reflectFunction($identifierName);
    }
    /**
     * @return list<ReflectionFunction>
     */
    public function reflectAllFunctions() : iterable
    {
        return $this->reflector->reflectAllFunctions();
    }
    public function reflectConstant(string $identifierName) : ReflectionConstant
    {
        return $this->reflector->reflectConstant($identifierName);
    }
    /**
     * @return list<ReflectionConstant>
     */
    public function reflectAllConstants() : iterable
    {
        return $this->reflector->reflectAllConstants();
    }
}
