<?php

declare (strict_types=1);
namespace PHPStan\Rules\Pure;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\MethodReturnStatementsNode;
use PHPStan\Rules\Rule;
use function sprintf;
/**
 * @implements Rule<MethodReturnStatementsNode>
 */
final class PureMethodRule implements Rule
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
        return MethodReturnStatementsNode::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $method = $node->getMethodReflection();
        return $this->check->check(sprintf('Method %s::%s()', $method->getDeclaringClass()->getDisplayName(), $method->getName()), 'Method', $method, $method->getParameters(), $method->getReturnType(), $node->getImpurePoints(), $node->getStatementResult()->getThrowPoints(), $node->getStatements(), $method->isConstructor());
    }
}
