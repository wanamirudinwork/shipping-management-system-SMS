<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection\Adapter;

use Attribute;
use PhpParser\Node\Expr;
use ReflectionAttribute as CoreReflectionAttribute;
use PHPStan\BetterReflection\Reflection\ReflectionAttribute as BetterReflectionAttribute;
/** @template-extends CoreReflectionAttribute<object> */
final class ReflectionAttribute extends CoreReflectionAttribute
{
    /**
     * @var BetterReflectionAttribute
     */
    private $betterReflectionAttribute;
    public function __construct(BetterReflectionAttribute $betterReflectionAttribute)
    {
        $this->betterReflectionAttribute = $betterReflectionAttribute;
    }
    /** @psalm-mutation-free */
    public function getName() : string
    {
        return $this->betterReflectionAttribute->getName();
    }
    /**
     * @return int-mask-of<Attribute::TARGET_*>
     *
     * @psalm-mutation-free
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function getTarget() : int
    {
        return $this->betterReflectionAttribute->getTarget();
    }
    /** @psalm-mutation-free */
    public function isRepeated() : bool
    {
        return $this->betterReflectionAttribute->isRepeated();
    }
    /**
     * @deprecated Use getArgumentsExpressions()
     * @return array<int|string, mixed>
     */
    public function getArguments() : array
    {
        return $this->betterReflectionAttribute->getArguments();
    }
    /** @return array<int|string, Expr> */
    public function getArgumentsExpressions() : array
    {
        return $this->betterReflectionAttribute->getArgumentsExpressions();
    }
    public function newInstance() : object
    {
        $class = $this->getName();
        return new $class(...$this->getArguments());
    }
    /** @return non-empty-string */
    public function __toString() : string
    {
        return $this->betterReflectionAttribute->__toString();
    }
}
