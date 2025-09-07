<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PhpParser\Node;
use PHPStan\Node\VirtualNode;
/**
 * @phpstan-type ImpurePointIdentifier = 'echo'|'die'|'exit'|'propertyAssign'|'propertyAssignByRef'|'propertyUnset'|'methodCall'|'new'|'functionCall'|'include'|'require'|'print'|'eval'|'superglobal'|'yield'|'yieldFrom'|'static'|'global'|'betweenPhpTags'
 * @api
 * @final
 */
class ImpurePoint
{
    /**
     * @var Scope
     */
    private $scope;
    /**
     * @var Node\Expr|Node\Stmt|VirtualNode
     */
    private $node;
    /**
     * @var ImpurePointIdentifier
     */
    private $identifier;
    /**
     * @var string
     */
    private $description;
    /**
     * @var bool
     */
    private $certain;
    /**
     * @param Node\Expr|Node\Stmt|VirtualNode $node
     * @param ImpurePointIdentifier $identifier
     */
    public function __construct(\PHPStan\Analyser\Scope $scope, Node $node, string $identifier, string $description, bool $certain)
    {
        $this->scope = $scope;
        $this->node = $node;
        $this->identifier = $identifier;
        $this->description = $description;
        $this->certain = $certain;
    }
    public function getScope() : \PHPStan\Analyser\Scope
    {
        return $this->scope;
    }
    /**
     * @return Node\Expr|Node\Stmt|VirtualNode
     */
    public function getNode()
    {
        return $this->node;
    }
    /**
     * @return ImpurePointIdentifier
     */
    public function getIdentifier() : string
    {
        return $this->identifier;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function isCertain() : bool
    {
        return $this->certain;
    }
}
