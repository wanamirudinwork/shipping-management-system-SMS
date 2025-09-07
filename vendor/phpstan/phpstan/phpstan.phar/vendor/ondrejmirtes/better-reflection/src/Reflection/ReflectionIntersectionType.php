<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use PhpParser\Node;
use PhpParser\Node\IntersectionType;
use PHPStan\BetterReflection\Reflector\Reflector;
use function array_map;
use function assert;
use function implode;
/** @psalm-immutable */
class ReflectionIntersectionType extends \PHPStan\BetterReflection\Reflection\ReflectionType
{
    /** @var non-empty-list<ReflectionNamedType> */
    private $types;
    /** @internal
     * @param \PHPStan\BetterReflection\Reflection\ReflectionParameter|\PHPStan\BetterReflection\Reflection\ReflectionMethod|\PHPStan\BetterReflection\Reflection\ReflectionFunction|\PHPStan\BetterReflection\Reflection\ReflectionEnum|\PHPStan\BetterReflection\Reflection\ReflectionProperty|\PHPStan\BetterReflection\Reflection\ReflectionClassConstant $owner */
    public function __construct(Reflector $reflector, $owner, IntersectionType $type)
    {
        /** @var non-empty-list<ReflectionNamedType> $types */
        $types = array_map(static function ($type) use($reflector, $owner) : \PHPStan\BetterReflection\Reflection\ReflectionNamedType {
            $type = \PHPStan\BetterReflection\Reflection\ReflectionType::createFromNode($reflector, $owner, $type);
            assert($type instanceof \PHPStan\BetterReflection\Reflection\ReflectionNamedType);
            return $type;
        }, $type->types);
        $this->types = $types;
    }
    /** @internal
     * @param \PHPStan\BetterReflection\Reflection\ReflectionParameter|\PHPStan\BetterReflection\Reflection\ReflectionMethod|\PHPStan\BetterReflection\Reflection\ReflectionFunction|\PHPStan\BetterReflection\Reflection\ReflectionEnum|\PHPStan\BetterReflection\Reflection\ReflectionProperty|\PHPStan\BetterReflection\Reflection\ReflectionClassConstant $owner
     * @return $this */
    public function withOwner($owner)
    {
        $clone = clone $this;
        foreach ($clone->types as $typeNo => $innerType) {
            $clone->types[$typeNo] = $innerType->withOwner($owner);
        }
        return $clone;
    }
    /** @return non-empty-list<ReflectionNamedType> */
    public function getTypes() : array
    {
        return $this->types;
    }
    /** @return false */
    public function allowsNull() : bool
    {
        return \false;
    }
    /** @return non-empty-string */
    public function __toString() : string
    {
        // @infection-ignore-all UnwrapArrayMap: It works without array_map() as well but this is less magical
        return implode('&', array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionNamedType $type) : string {
            return $type->__toString();
        }, $this->types));
    }
}
