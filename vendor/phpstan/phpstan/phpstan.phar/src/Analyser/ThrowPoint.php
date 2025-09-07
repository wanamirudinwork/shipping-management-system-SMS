<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Throwable;
/**
 * @api
 * @final
 */
class ThrowPoint
{
    /**
     * @var MutatingScope
     */
    private $scope;
    /**
     * @var Type
     */
    private $type;
    /**
     * @var Node\Expr|Node\Stmt
     */
    private $node;
    /**
     * @var bool
     */
    private $explicit;
    /**
     * @var bool
     */
    private $canContainAnyThrowable;
    /**
     * @param Node\Expr|Node\Stmt $node
     */
    private function __construct(\PHPStan\Analyser\MutatingScope $scope, Type $type, Node $node, bool $explicit, bool $canContainAnyThrowable)
    {
        $this->scope = $scope;
        $this->type = $type;
        $this->node = $node;
        $this->explicit = $explicit;
        $this->canContainAnyThrowable = $canContainAnyThrowable;
    }
    /**
     * @param Node\Expr|Node\Stmt $node
     */
    public static function createExplicit(\PHPStan\Analyser\MutatingScope $scope, Type $type, Node $node, bool $canContainAnyThrowable) : self
    {
        return new self($scope, $type, $node, \true, $canContainAnyThrowable);
    }
    /**
     * @param Node\Expr|Node\Stmt $node
     */
    public static function createImplicit(\PHPStan\Analyser\MutatingScope $scope, Node $node) : self
    {
        return new self($scope, new ObjectType(Throwable::class), $node, \false, \true);
    }
    public function getScope() : \PHPStan\Analyser\MutatingScope
    {
        return $this->scope;
    }
    public function getType() : Type
    {
        return $this->type;
    }
    /**
     * @return Node\Expr|Node\Stmt
     */
    public function getNode()
    {
        return $this->node;
    }
    public function isExplicit() : bool
    {
        return $this->explicit;
    }
    public function canContainAnyThrowable() : bool
    {
        return $this->canContainAnyThrowable;
    }
    public function subtractCatchType(Type $catchType) : self
    {
        return new self($this->scope, TypeCombinator::remove($this->type, $catchType), $this->node, $this->explicit, $this->canContainAnyThrowable);
    }
}
