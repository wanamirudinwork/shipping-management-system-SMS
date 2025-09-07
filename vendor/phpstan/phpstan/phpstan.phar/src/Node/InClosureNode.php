<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\NodeAbstract;
use PHPStan\Type\ClosureType;
/**
 * @api
 * @final
 */
class InClosureNode extends NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /**
     * @var ClosureType
     */
    private $closureType;
    /**
     * @var Node\Expr\Closure
     */
    private $originalNode;
    public function __construct(ClosureType $closureType, Closure $originalNode)
    {
        $this->closureType = $closureType;
        parent::__construct($originalNode->getAttributes());
        $this->originalNode = $originalNode;
    }
    public function getClosureType() : ClosureType
    {
        return $this->closureType;
    }
    public function getOriginalNode() : Closure
    {
        return $this->originalNode;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_InClosureNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
