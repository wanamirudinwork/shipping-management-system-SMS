<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\NodeAbstract;
use PHPStan\Type\ClosureType;
/**
 * @api
 * @final
 */
class InArrowFunctionNode extends NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /**
     * @var ClosureType
     */
    private $closureType;
    /**
     * @var Node\Expr\ArrowFunction
     */
    private $originalNode;
    public function __construct(ClosureType $closureType, ArrowFunction $originalNode)
    {
        $this->closureType = $closureType;
        parent::__construct($originalNode->getAttributes());
        $this->originalNode = $originalNode;
    }
    public function getClosureType() : ClosureType
    {
        return $this->closureType;
    }
    public function getOriginalNode() : Node\Expr\ArrowFunction
    {
        return $this->originalNode;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_InArrowFunctionNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
