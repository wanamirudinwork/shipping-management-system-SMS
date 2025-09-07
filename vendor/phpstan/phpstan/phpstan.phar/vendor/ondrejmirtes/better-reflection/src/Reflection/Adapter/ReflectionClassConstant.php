<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use OutOfBoundsException;
use PhpParser\Node\Expr;
use ReflectionClassConstant as CoreReflectionClassConstant;
use ReflectionType as CoreReflectionType;
use ReturnTypeWillChange;
use PHPStan\BetterReflection\Reflection\ReflectionAttribute as BetterReflectionAttribute;
use PHPStan\BetterReflection\Reflection\ReflectionClassConstant as BetterReflectionClassConstant;
use PHPStan\BetterReflection\Reflection\ReflectionEnumCase as BetterReflectionEnumCase;
use ValueError;
use function array_map;
use function sprintf;
/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-immutable
 */
final class ReflectionClassConstant extends CoreReflectionClassConstant
{
    /** @internal */
    public const IS_PUBLIC_COMPATIBILITY = 1;
    /** @internal */
    public const IS_PROTECTED_COMPATIBILITY = 2;
    /** @internal */
    public const IS_PRIVATE_COMPATIBILITY = 4;
    /** @internal */
    public const IS_FINAL_COMPATIBILITY = 32;
    /**
     * @var BetterReflectionClassConstant|BetterReflectionEnumCase
     */
    private $betterClassConstantOrEnumCase;
    /**
     * @param BetterReflectionClassConstant|BetterReflectionEnumCase $betterClassConstantOrEnumCase
     */
    public function __construct($betterClassConstantOrEnumCase)
    {
        $this->betterClassConstantOrEnumCase = $betterClassConstantOrEnumCase;
        unset($this->name);
        unset($this->class);
    }
    public function getName() : string
    {
        return $this->betterClassConstantOrEnumCase->getName();
    }
    /** @psalm-mutation-free */
    public function hasType() : bool
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            return \false;
        }
        return $this->betterClassConstantOrEnumCase->hasType();
    }
    /**
     * @psalm-mutation-free
     * @return ReflectionUnionType|ReflectionNamedType|ReflectionIntersectionType|null
     */
    public function getType() : ?CoreReflectionType
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            return null;
        }
        return \PHPStan\BetterReflection\Reflection\Adapter\ReflectionType::fromTypeOrNull($this->betterClassConstantOrEnumCase->getType());
    }
    /**
     * @deprecated Use getValueExpression()
     */
    #[\ReturnTypeWillChange]
    public function getValue()
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            throw new \PHPStan\BetterReflection\Reflection\Adapter\Exception\NotImplemented('Not implemented');
        }
        return $this->betterClassConstantOrEnumCase->getValue();
    }
    /**
     * @deprecated Use getValueExpression()
     */
    public function getValueExpr() : Expr
    {
        return $this->getValueExpression();
    }
    public function getValueExpression() : Expr
    {
        return $this->betterClassConstantOrEnumCase->getValueExpression();
    }
    public function isPublic() : bool
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            return \true;
        }
        return $this->betterClassConstantOrEnumCase->isPublic();
    }
    public function isPrivate() : bool
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            return \false;
        }
        return $this->betterClassConstantOrEnumCase->isPrivate();
    }
    public function isProtected() : bool
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            return \false;
        }
        return $this->betterClassConstantOrEnumCase->isProtected();
    }
    public function getModifiers() : int
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            return \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClassConstant::IS_PUBLIC_COMPATIBILITY;
        }
        return $this->betterClassConstantOrEnumCase->getModifiers();
    }
    public function getDeclaringClass() : \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($this->betterClassConstantOrEnumCase->getDeclaringClass());
        }
        return new \PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass($this->betterClassConstantOrEnumCase->getImplementingClass());
    }
    /**
     * Returns the doc comment for this constant
     *
     * @return string|false
     */
    #[\ReturnTypeWillChange]
    public function getDocComment()
    {
        return $this->betterClassConstantOrEnumCase->getDocComment() ?? \false;
    }
    /**
     * To string
     *
     * @link https://php.net/manual/en/reflector.tostring.php
     *
     * @return non-empty-string
     */
    public function __toString() : string
    {
        return $this->betterClassConstantOrEnumCase->__toString();
    }
    /**
     * @param class-string|null $name
     *
     * @return list<ReflectionAttribute|FakeReflectionAttribute>
     */
    public function getAttributes(?string $name = null, int $flags = 0) : array
    {
        if ($flags !== 0 && $flags !== \PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttribute::IS_INSTANCEOF) {
            throw new ValueError('Argument #2 ($flags) must be a valid attribute filter flag');
        }
        if ($name !== null && $flags !== 0) {
            $attributes = $this->betterClassConstantOrEnumCase->getAttributesByInstance($name);
        } elseif ($name !== null) {
            $attributes = $this->betterClassConstantOrEnumCase->getAttributesByName($name);
        } else {
            $attributes = $this->betterClassConstantOrEnumCase->getAttributes();
        }
        /** @psalm-suppress ImpureFunctionCall */
        return array_map(static function (BetterReflectionAttribute $betterReflectionAttribute) {
            return \PHPStan\BetterReflection\Reflection\Adapter\ReflectionAttributeFactory::create($betterReflectionAttribute);
        }, $attributes);
    }
    public function isFinal() : bool
    {
        if ($this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase) {
            return \true;
        }
        return $this->betterClassConstantOrEnumCase->isFinal();
    }
    public function isEnumCase() : bool
    {
        return $this->betterClassConstantOrEnumCase instanceof BetterReflectionEnumCase;
    }
    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name === 'name') {
            return $this->betterClassConstantOrEnumCase->getName();
        }
        if ($name === 'class') {
            return $this->getDeclaringClass()->getName();
        }
        throw new OutOfBoundsException(sprintf('Property %s::$%s does not exist.', self::class, $name));
    }
}
