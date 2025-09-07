<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\UnionType;
use PHPStan\BetterReflection\Reflector\Reflector;
use function array_map;
use function assert;
use function implode;
use function sprintf;
/** @psalm-immutable */
class ReflectionUnionType extends \PHPStan\BetterReflection\Reflection\ReflectionType
{
    /** @var non-empty-list<ReflectionNamedType|ReflectionIntersectionType> */
    private $types;
    /** @internal
     * @param \PHPStan\BetterReflection\Reflection\ReflectionParameter|\PHPStan\BetterReflection\Reflection\ReflectionMethod|\PHPStan\BetterReflection\Reflection\ReflectionFunction|\PHPStan\BetterReflection\Reflection\ReflectionEnum|\PHPStan\BetterReflection\Reflection\ReflectionProperty|\PHPStan\BetterReflection\Reflection\ReflectionClassConstant $owner */
    public function __construct(Reflector $reflector, $owner, UnionType $type)
    {
        /** @var non-empty-list<ReflectionNamedType|ReflectionIntersectionType> $types */
        $types = array_map(static function ($type) use($reflector, $owner) {
            $type = \PHPStan\BetterReflection\Reflection\ReflectionType::createFromNode($reflector, $owner, $type);
            assert($type instanceof \PHPStan\BetterReflection\Reflection\ReflectionNamedType || $type instanceof \PHPStan\BetterReflection\Reflection\ReflectionIntersectionType);
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
    /** @return non-empty-list<ReflectionNamedType|ReflectionIntersectionType> */
    public function getTypes() : array
    {
        return $this->types;
    }
    public function allowsNull() : bool
    {
        foreach ($this->types as $type) {
            if ($type->allowsNull()) {
                return \true;
            }
        }
        return \false;
    }
    /** @return non-empty-string */
    public function __toString() : string
    {
        return implode('|', array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionType $type) : string {
            if ($type instanceof \PHPStan\BetterReflection\Reflection\ReflectionIntersectionType) {
                return sprintf('(%s)', $type->__toString());
            }
            return $type->__toString();
        }, $this->types));
    }
}
