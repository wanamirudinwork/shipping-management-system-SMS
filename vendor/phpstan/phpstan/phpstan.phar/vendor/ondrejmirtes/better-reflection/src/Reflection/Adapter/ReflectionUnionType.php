<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use ReflectionUnionType as CoreReflectionUnionType;
use PHPStan\BetterReflection\Reflection\ReflectionType as BetterReflectionType;
use PHPStan\BetterReflection\Reflection\ReflectionUnionType as BetterReflectionUnionType;
use function array_map;
use function assert;
/** @psalm-immutable */
final class ReflectionUnionType extends CoreReflectionUnionType
{
    /**
     * @var BetterReflectionUnionType
     */
    private $betterReflectionType;
    public function __construct(BetterReflectionUnionType $betterReflectionType)
    {
        $this->betterReflectionType = $betterReflectionType;
    }
    /** @return non-empty-list<ReflectionNamedType|ReflectionIntersectionType> */
    public function getTypes() : array
    {
        return array_map(static function (BetterReflectionType $type) {
            $adapterType = \PHPStan\BetterReflection\Reflection\Adapter\ReflectionType::fromType($type);
            assert($adapterType instanceof \PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType || $adapterType instanceof \PHPStan\BetterReflection\Reflection\Adapter\ReflectionIntersectionType);
            return $adapterType;
        }, $this->betterReflectionType->getTypes());
    }
    /** @return non-empty-string */
    public function __toString() : string
    {
        return $this->betterReflectionType->__toString();
    }
    public function allowsNull() : bool
    {
        return $this->betterReflectionType->allowsNull();
    }
}
