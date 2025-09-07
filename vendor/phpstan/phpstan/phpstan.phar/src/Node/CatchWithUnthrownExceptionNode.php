<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Stmt\Catch_;
use PhpParser\NodeAbstract;
use PHPStan\Type\Type;
/**
 * @api
 * @final
 */
class CatchWithUnthrownExceptionNode extends NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /**
     * @var Catch_
     */
    private $originalNode;
    /**
     * @var Type
     */
    private $caughtType;
    /**
     * @var Type
     */
    private $originalCaughtType;
    public function __construct(Catch_ $originalNode, Type $caughtType, Type $originalCaughtType)
    {
        $this->originalNode = $originalNode;
        $this->caughtType = $caughtType;
        $this->originalCaughtType = $originalCaughtType;
        parent::__construct($originalNode->getAttributes());
    }
    public function getOriginalNode() : Catch_
    {
        return $this->originalNode;
    }
    public function getCaughtType() : Type
    {
        return $this->caughtType;
    }
    public function getOriginalCaughtType() : Type
    {
        return $this->originalCaughtType;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_CatchWithUnthrownExceptionNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
