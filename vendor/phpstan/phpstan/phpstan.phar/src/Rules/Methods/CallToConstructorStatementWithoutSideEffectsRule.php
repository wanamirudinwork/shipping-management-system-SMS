<?php

declare (strict_types=1);
namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\NoopExpressionNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\NeverType;
use function sprintf;
/**
 * @implements Rule<NoopExpressionNode>
 */
final class CallToConstructorStatementWithoutSideEffectsRule implements Rule
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var bool
     */
    private $reportNoConstructor;
    public function __construct(ReflectionProvider $reflectionProvider, bool $reportNoConstructor)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->reportNoConstructor = $reportNoConstructor;
    }
    public function getNodeType() : string
    {
        return NoopExpressionNode::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $instantiation = $node->getOriginalExpr();
        if (!$instantiation instanceof Node\Expr\New_) {
            return [];
        }
        if (!$instantiation->class instanceof Node\Name) {
            return [];
        }
        $className = $scope->resolveName($instantiation->class);
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->hasConstructor()) {
            if ($this->reportNoConstructor) {
                return [RuleErrorBuilder::message(sprintf('Call to new %s() on a separate line has no effect.', $classReflection->getDisplayName()))->identifier('new.resultUnused')->build()];
            }
            return [];
        }
        $constructor = $classReflection->getConstructor();
        $methodResult = $scope->getType($instantiation);
        if ($methodResult instanceof NeverType && $methodResult->isExplicit()) {
            return [];
        }
        return [RuleErrorBuilder::message(sprintf('Call to %s::%s() on a separate line has no effect.', $classReflection->getDisplayName(), $constructor->getName()))->identifier('new.resultUnused')->build()];
    }
}
