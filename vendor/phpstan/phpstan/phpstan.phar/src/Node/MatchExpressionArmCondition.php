<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
/**
 * @api
 * @final
 */
class MatchExpressionArmCondition
{
    /**
     * @var Expr
     */
    private $condition;
    /**
     * @var Scope
     */
    private $scope;
    /**
     * @var int
     */
    private $line;
    public function __construct(Expr $condition, Scope $scope, int $line)
    {
        $this->condition = $condition;
        $this->scope = $scope;
        $this->line = $line;
    }
    public function getCondition() : Expr
    {
        return $this->condition;
    }
    public function getScope() : Scope
    {
        return $this->scope;
    }
    public function getLine() : int
    {
        return $this->line;
    }
}
