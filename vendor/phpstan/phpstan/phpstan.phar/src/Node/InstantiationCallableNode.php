<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
/**
 * @api
 * @final
 */
class InstantiationCallableNode extends Expr implements \PHPStan\Node\VirtualNode
{
    /**
     * @var Name|Expr
     */
    private $class;
    /**
     * @var Expr\New_
     */
    private $originalNode;
    /**
     * @param Name|Expr $class
     */
    public function __construct($class, Expr\New_ $originalNode)
    {
        $this->class = $class;
        $this->originalNode = $originalNode;
        parent::__construct($this->originalNode->getAttributes());
    }
    /**
     * @return Expr|Name
     */
    public function getClass()
    {
        return $this->class;
    }
    public function getOriginalNode() : Expr\New_
    {
        return $this->originalNode;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_InstantiationCallableNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
