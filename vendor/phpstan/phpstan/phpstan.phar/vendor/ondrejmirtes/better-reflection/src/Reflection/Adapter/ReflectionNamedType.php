<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use ReflectionNamedType as CoreReflectionNamedType;
use PHPStan\BetterReflection\Reflection\ReflectionNamedType as BetterReflectionNamedType;
use function strtolower;
/** @psalm-immutable */
final class ReflectionNamedType extends CoreReflectionNamedType
{
    /**
     * @var BetterReflectionNamedType
     */
    private $betterReflectionType;
    /**
     * @var bool
     */
    private $allowsNull;
    public function __construct(BetterReflectionNamedType $betterReflectionType, bool $allowsNull)
    {
        $this->betterReflectionType = $betterReflectionType;
        $this->allowsNull = $allowsNull;
    }
    public function getName() : string
    {
        return $this->betterReflectionType->getName();
    }
    /** @return non-empty-string */
    public function __toString() : string
    {
        $type = strtolower($this->betterReflectionType->getName());
        if (!$this->allowsNull || $type === 'mixed' || $type === 'null') {
            return $this->betterReflectionType->__toString();
        }
        return '?' . $this->betterReflectionType->__toString();
    }
    public function allowsNull() : bool
    {
        return $this->allowsNull;
    }
    public function isBuiltin() : bool
    {
        $type = strtolower($this->betterReflectionType->getName());
        if ($type === 'self' || $type === 'parent' || $type === 'static') {
            return \false;
        }
        return $this->betterReflectionType->isBuiltin();
    }
    public function isIdentifier() : bool
    {
        return $this->betterReflectionType->isIdentifier();
    }
}
