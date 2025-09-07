<?php

declare (strict_types=1);
namespace PHPStan\Node\Expr;

use PhpParser\Node\Expr;
use PHPStan\Node\VirtualNode;
final class ParameterVariableOriginalValueExpr extends Expr implements VirtualNode
{
    /**
     * @var string
     */
    private $variableName;
    public function __construct(string $variableName)
    {
        $this->variableName = $variableName;
        parent::__construct([]);
    }
    public function getVariableName() : string
    {
        return $this->variableName;
    }
    public function getType() : string
    {
        return 'PHPStan_Node_ParameterVariableOriginalValueExpr';
    }
    /**
     * @return string[]
     */
    public function getSubNodeNames() : array
    {
        return [];
    }
}
