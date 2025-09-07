<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr;
use PhpParser\NodeAbstract;
final class VariableAssignNode extends NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /**
     * @var Expr\Variable
     */
    private $variable;
    /**
     * @var Expr
     */
    private $assignedExpr;
    /**
     * @var bool
     */
    private $assignOp;
    public function __construct(Expr\Variable $variable, Expr $assignedExpr, bool $assignOp)
    {
        $this->variable = $variable;
        $this->assignedExpr = $assignedExpr;
        $this->assignOp = $assignOp;
        parent::__construct($variable->getAttributes());
    }
    public function getVariable() : Expr\Variable
    {
        return $this->variable;
    }
    public function getAssignedExpr() : Expr
    {
        return $this->assignedExpr;
    }
    public function isAssignOp() : bool
    {
        return $this->assignOp;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_VariableAssignNodeNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
