<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeAbstract;
use PHPStan\Node\Method\MethodCall;
use PHPStan\Reflection\ClassReflection;
/**
 * @api
 * @final
 */
class ClassMethodsNode extends NodeAbstract implements \PHPStan\Node\VirtualNode
{
    /**
     * @var ClassLike
     */
    private $class;
    /**
     * @var ClassMethod[]
     */
    private $methods;
    /**
     * @var array<int, MethodCall>
     */
    private $methodCalls;
    /**
     * @var ClassReflection
     */
    private $classReflection;
    /**
     * @param ClassMethod[] $methods
     * @param array<int, MethodCall> $methodCalls
     */
    public function __construct(ClassLike $class, array $methods, array $methodCalls, ClassReflection $classReflection)
    {
        $this->class = $class;
        $this->methods = $methods;
        $this->methodCalls = $methodCalls;
        $this->classReflection = $classReflection;
        parent::__construct($class->getAttributes());
    }
    public function getClass() : ClassLike
    {
        return $this->class;
    }
    /**
     * @return ClassMethod[]
     */
    public function getMethods() : array
    {
        return $this->methods;
    }
    /**
     * @return array<int, MethodCall>
     */
    public function getMethodCalls() : array
    {
        return $this->methodCalls;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_ClassMethodsNode';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
    public function getClassReflection() : ClassReflection
    {
        return $this->classReflection;
    }
}
