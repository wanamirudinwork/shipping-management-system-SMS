<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Reflection\ClassReflection;
/**
 * @api
 * @final
 */
class InClassNode extends Node\Stmt implements \PHPStan\Node\VirtualNode
{
    /**
     * @var ClassLike
     */
    private $originalNode;
    /**
     * @var ClassReflection
     */
    private $classReflection;
    public function __construct(ClassLike $originalNode, ClassReflection $classReflection)
    {
        $this->originalNode = $originalNode;
        $this->classReflection = $classReflection;
        parent::__construct($originalNode->getAttributes());
    }
    public function getOriginalNode() : ClassLike
    {
        return $this->originalNode;
    }
    public function getClassReflection() : ClassReflection
    {
        return $this->classReflection;
    }
    public function getType() : string
    {
        return 'PHPStan_Stmt_InClassNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
