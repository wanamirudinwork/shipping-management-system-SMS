<?php

declare (strict_types=1);
namespace PHPStan\Node;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
/**
 * @api
 * @final
 */
class MatchExpressionArmBody
{
    /**
     * @var Scope
     */
    private $scope;
    /**
     * @var Expr
     */
    private $body;
    public function __construct(Scope $scope, Expr $body)
    {
        $this->scope = $scope;
        $this->body = $body;
    }
    public function getScope() : Scope
    {
        return $this->scope;
    }
    public function getBody() : Expr
    {
        return $this->body;
    }
}
