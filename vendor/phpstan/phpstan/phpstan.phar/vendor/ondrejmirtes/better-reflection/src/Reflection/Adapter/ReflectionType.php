<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use ReflectionType as CoreReflectionType;
use PHPStan\BetterReflection\Reflection\ReflectionIntersectionType as BetterReflectionIntersectionType;
use PHPStan\BetterReflection\Reflection\ReflectionNamedType as BetterReflectionNamedType;
use PHPStan\BetterReflection\Reflection\ReflectionType as BetterReflectionType;
use PHPStan\BetterReflection\Reflection\ReflectionUnionType as BetterReflectionUnionType;
use function array_filter;
use function array_values;
use function count;
/** @psalm-immutable */
abstract class ReflectionType extends CoreReflectionType
{
    /** @psalm-pure
     * @param BetterReflectionUnionType|BetterReflectionNamedType|BetterReflectionIntersectionType|null $betterReflectionType
     * @return \PHPStan\BetterReflection\Reflection\Adapter\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\Adapter\ReflectionIntersectionType|null */
    public static function fromTypeOrNull($betterReflectionType)
    {
        return $betterReflectionType !== null ? self::fromType($betterReflectionType) : null;
    }
    /**
     * @internal
     *
     * @psalm-pure
     * @param BetterReflectionNamedType|BetterReflectionUnionType|BetterReflectionIntersectionType $betterReflectionType
     * @return \PHPStan\BetterReflection\Reflection\Adapter\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\Adapter\ReflectionIntersectionType
     */
    public static function fromType($betterReflectionType)
    {
        if ($betterReflectionType instanceof BetterReflectionUnionType) {
            // php-src has this weird behavior where a union type composed of a single type `T`
            // together with `null` means that a `ReflectionNamedType` for `?T` is produced,
            // rather than `T|null`. This is done to keep BC compatibility with PHP 7.1 (which
            // introduced nullable types), but at reflection level, this is mostly a nuisance.
            // In order to keep parity with core, we stashed this weird behavior in here.
            $nonNullTypes = array_values(array_filter($betterReflectionType->getTypes(), static function (BetterReflectionType $type) : bool {
                return !($type instanceof BetterReflectionNamedType && $type->getName() === 'null');
            }));
            if ($betterReflectionType->allowsNull() && count($nonNullTypes) === 1 && $nonNullTypes[0] instanceof BetterReflectionNamedType) {
                return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType($nonNullTypes[0], \true);
            }
            return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionUnionType($betterReflectionType);
        }
        if ($betterReflectionType instanceof BetterReflectionIntersectionType) {
            return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionIntersectionType($betterReflectionType);
        }
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionNamedType($betterReflectionType, $betterReflectionType->allowsNull());
    }
}
