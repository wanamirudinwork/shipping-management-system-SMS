<?php

declare (strict_types=1);
namespace PHPStan\Rules\Properties;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use function sprintf;
/**
 * @implements Rule<Node\Expr>
 */
final class ReadingWriteOnlyPropertiesRule implements Rule
{
    /**
     * @var PropertyDescriptor
     */
    private $propertyDescriptor;
    /**
     * @var PropertyReflectionFinder
     */
    private $propertyReflectionFinder;
    /**
     * @var RuleLevelHelper
     */
    private $ruleLevelHelper;
    /**
     * @var bool
     */
    private $checkThisOnly;
    public function __construct(\PHPStan\Rules\Properties\PropertyDescriptor $propertyDescriptor, \PHPStan\Rules\Properties\PropertyReflectionFinder $propertyReflectionFinder, RuleLevelHelper $ruleLevelHelper, bool $checkThisOnly)
    {
        $this->propertyDescriptor = $propertyDescriptor;
        $this->propertyReflectionFinder = $propertyReflectionFinder;
        $this->ruleLevelHelper = $ruleLevelHelper;
        $this->checkThisOnly = $checkThisOnly;
    }
    public function getNodeType() : string
    {
        return Node\Expr::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        if (!$node instanceof Node\Expr\PropertyFetch && !$node instanceof Node\Expr\StaticPropertyFetch) {
            return [];
        }
        if ($node instanceof Node\Expr\PropertyFetch && $this->checkThisOnly && !$this->ruleLevelHelper->isThis($node->var)) {
            return [];
        }
        if ($scope->isInExpressionAssign($node)) {
            return [];
        }
        $propertyReflection = $this->propertyReflectionFinder->findPropertyReflectionFromNode($node, $scope);
        if ($propertyReflection === null) {
            return [];
        }
        if (!$scope->canAccessProperty($propertyReflection)) {
            return [];
        }
        if (!$propertyReflection->isReadable()) {
            $propertyDescription = $this->propertyDescriptor->describeProperty($propertyReflection, $scope, $node);
            return [RuleErrorBuilder::message(sprintf('%s is not readable.', $propertyDescription))->identifier('property.writeOnly')->build()];
        }
        return [];
    }
}
