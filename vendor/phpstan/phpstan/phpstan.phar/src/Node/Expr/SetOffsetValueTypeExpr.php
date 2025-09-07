<?php

declare (strict_types=1);
namespace PHPStan\Node\Expr;

use PhpParser\Node\Expr;
use PHPStan\Node\VirtualNode;
final class SetOffsetValueTypeExpr extends Expr implements VirtualNode
{
    /**
     * @var Expr
     */
    private $var;
    /**
     * @var ?Expr
     */
    private $dim;
    /**
     * @var Expr
     */
    private $value;
    public function __construct(Expr $var, ?Expr $dim, Expr $value)
    {
        $this->var = $var;
        $this->dim = $dim;
        $this->value = $value;
        parent::__construct([]);
    }
    public function getVar() : Expr
    {
        return $this->var;
    }
    public function getDim() : ?Expr
    {
        return $this->dim;
    }
    public function getValue() : Expr
    {
        return $this->value;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_SetOffsetValueTypeExpr';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
