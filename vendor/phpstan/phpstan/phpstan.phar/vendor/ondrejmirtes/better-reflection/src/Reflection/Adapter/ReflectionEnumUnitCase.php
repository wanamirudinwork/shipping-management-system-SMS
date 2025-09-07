<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use OutOfBoundsException;
use ReflectionEnumUnitCase as CoreReflectionEnumUnitCase;
use PHPStan\BetterReflection\Reflection\ReflectionAttribute as BetterReflectionAttribute;
use PHPStan\BetterReflection\Reflection\ReflectionEnumCase as BetterReflectionEnumCase;
use UnitEnum;
use ValueError;
use function array_map;
use function sprintf;
/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-immutable
 */
final class ReflectionEnumUnitCase extends CoreReflectionEnumUnitCase
{
    public function __construct(private BetterReflectionEnumCase $betterReflectionEnumCase)
    {
        unset($this->name);
        unset($this->class);
    }
    /**
     * Get the name of the reflection (e.g. if this is a ReflectionClass this
     * will be the class name).
     */
    public function getName() : string
    {
        return $this->betterReflectionEnumCase->getName();
    }
    public function hasType() : bool
    {
        return \false;
    }
    public function getType() : \PHPStan\BetterReflection\Reflection\Adapter\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\Adapter\ReflectionIntersectionType|null
    {
        return null;
    }
    public function getValue() : UnitEnum
    {
        throw new \PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented('Not implemented');
    }
    public function isPublic() : bool
    {
        return \true;
    }
    public function isPrivate() : bool
    {
        return \false;
    }
    public function isProtected() : bool
    {
        return \false;
    }
    public function getModifiers() : int
    {
        return self::IS_PUBLIC;
    }
    public function getDeclaringClass() : \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass
    {
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($this->betterReflectionEnumCase->getDeclaringClass());
    }
    public function getDocComment() : string|false
    {
        return $this->betterReflectionEnumCase->getDocComment() ?? \false;
    }
    /** @return non-empty-string */
    public function __toString() : string
    {
        return $this->betterReflectionEnumCase->__toString();
    }
    /**
     * @param class-string|null $name
     *
     * @return list<ReflectionAttribute|FakeReflectionAttribute>
     */
    public function getAttributes(string|null $name = null, int $flags = 0) : array
    {
        if ($flags !== 0 && $flags !== \PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute::IS_INSTANCEOF) {
            throw new ValueError('Argument #2 ($flags) must be a valid attribute filter flag');
        }
        if ($name !== null && $flags !== 0) {
            $attributes = $this->betterReflectionEnumCase->getAttributesByInstance($name);
        } elseif ($name !== null) {
            $attributes = $this->betterReflectionEnumCase->getAttributesByName($name);
        } else {
            $attributes = $this->betterReflectionEnumCase->getAttributes();
        }
        /** @psalm-suppress ImpureFunctionCall */
        return array_map(static fn(BetterReflectionAttribute $betterReflectionAttribute): \PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute|\PHPStan\BetterReflection\Reflection\Adapter\FakeReflectionAttribute => \PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttributeFactory::create($betterReflectionAttribute), $attributes);
    }
    public function isFinal() : bool
    {
        return \true;
    }
    public function isEnumCase() : bool
    {
        return \true;
    }
    public function getEnum() : \PHPStan\BetterReflection\Reflection\Adapter\ReflectionEnum
    {
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionEnum($this->betterReflectionEnumCase->getDeclaringEnum());
    }
    public function __get(string $name) : mixed
    {
        if ($name === 'name') {
            return $this->betterReflectionEnumCase->getName();
        }
        if ($name === 'class') {
            return $this->betterReflectionEnumCase->getDeclaringClass()->getName();
        }
        throw new OutOfBoundsException(sprintf('Property %s::$%s does not exist.', self::class, $name));
    }
}
