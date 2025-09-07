<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
/**
 * @api
 * @final
 */
class FunctionCallableNode extends Expr implements \PHPStan\Node\VirtualNode
{
    /**
     * @var Name|Expr
     */
    private $name;
    /**
     * @var Expr\FuncCall
     */
    private $originalNode;
    /**
     * @param Name|Expr $name
     */
    public function __construct($name, Expr\FuncCall $originalNode)
    {
        $this->name = $name;
        $this->originalNode = $originalNode;
        parent::__construct($this->originalNode->getAttributes());
    }
    /**
     * @return Expr|Name
     */
    public function getName()
    {
        return $this->name;
    }
    public function getOriginalNode() : Expr\FuncCall
    {
        return $this->originalNode;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_FunctionCallableNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
