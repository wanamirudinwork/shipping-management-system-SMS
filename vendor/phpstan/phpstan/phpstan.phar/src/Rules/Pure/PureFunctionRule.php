<?php

declare (strict_types=1);
namespace PHPStan\Rules\Pure;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FunctionReturnStatementsNode;
use PHPStan\Rules\Rule;
use function sprintf;
/**
 * @implements Rule<FunctionReturnStatementsNode>
 */
final class PureFunctionRule implements Rule
{
    /**
     * @var FunctionPurityCheck
     */
    private $check;
    public function __construct(\PHPStan\Rules\Pure\FunctionPurityCheck $check)
    {
        $this->check = $check;
    }
    public function getNodeType() : string
    {
        return FunctionReturnStatementsNode::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $function = $node->getFunctionReflection();
        return $this->check->check(sprintf('Function %s()', $function->getName()), 'Function', $function, $function->getParameters(), $function->getReturnType(), $node->getImpurePoints(), $node->getStatementResult()->getThrowPoints(), $node->getStatements(), \false);
    }
}
