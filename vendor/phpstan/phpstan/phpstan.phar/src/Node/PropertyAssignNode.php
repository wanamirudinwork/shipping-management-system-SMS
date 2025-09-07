<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr;
use PhpParser\NodeAbstract;
final class PropertyAssignNode extends NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /**
     * @var Expr\PropertyFetch|Expr\StaticPropertyFetch
     */
    private $propertyFetch;
    /**
     * @var Expr
     */
    private $assignedExpr;
    /**
     * @var bool
     */
    private $assignOp;
    /**
     * @param Expr\PropertyFetch|Expr\StaticPropertyFetch $propertyFetch
     */
    public function __construct($propertyFetch, Expr $assignedExpr, bool $assignOp)
    {
        $this->propertyFetch = $propertyFetch;
        $this->assignedExpr = $assignedExpr;
        $this->assignOp = $assignOp;
        parent::__construct($propertyFetch->getAttributes());
    }
    /**
     * @return Expr\PropertyFetch|Expr\StaticPropertyFetch
     */
    public function getPropertyFetch()
    {
        return $this->propertyFetch;
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
        return 'PHPStan_Node_PropertyAssignNodeNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
