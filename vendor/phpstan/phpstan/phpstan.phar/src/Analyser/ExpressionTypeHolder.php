<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PhpParser\Node\Expr;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
final class ExpressionTypeHolder
{
    /**
     * @var Expr
     */
    private $expr;
    /**
     * @var Type
     */
    private $type;
    /**
     * @var TrinaryLogic
     */
    private $certainty;
    public function __construct(Expr $expr, Type $type, TrinaryLogic $certainty)
    {
        $this->expr = $expr;
        $this->type = $type;
        $this->certainty = $certainty;
    }
    public static function createYes(Expr $expr, Type $type) : self
    {
        return new self($expr, $type, TrinaryLogic::createYes());
    }
    public static function createMaybe(Expr $expr, Type $type) : self
    {
        return new self($expr, $type, TrinaryLogic::createMaybe());
    }
    public function equals(self $other) : bool
    {
        if (!$this->certainty->equals($other->certainty)) {
            return \false;
        }
        return $this->type->equals($other->type);
    }
    public function and(self $other) : self
    {
        if ($this->getType()->equals($other->getType())) {
            $type = $this->getType();
        } else {
            $type = TypeCombinator::union($this->getType(), $other->getType());
        }
        return new self($this->expr, $type, $this->getCertainty()->and($other->getCertainty()));
    }
    public function getExpr() : Expr
    {
        return $this->expr;
    }
    public function getType() : Type
    {
        return $this->type;
    }
    public function getCertainty() : TrinaryLogic
    {
        return $this->certainty;
    }
}
