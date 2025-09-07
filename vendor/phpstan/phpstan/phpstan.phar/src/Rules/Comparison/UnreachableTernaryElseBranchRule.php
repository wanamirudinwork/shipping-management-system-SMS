<?php

declare (strict_types=1);
namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
/**
 * @implements Rule<Node\Expr\Ternary>
 */
final class UnreachableTernaryElseBranchRule implements Rule
{
    /**
     * @var ConstantConditionRuleHelper
     */
    private $helper;
    /**
     * @var bool
     */
    private $treatPhpDocTypesAsCertain;
    /**
     * @var bool
     */
    private $disable;
    /**
     * @var bool
     */
    private $treatPhpDocTypesAsCertainTip;
    public function __construct(\PHPStan\Rules\Comparison\ConstantConditionRuleHelper $helper, bool $treatPhpDocTypesAsCertain, bool $disable, bool $treatPhpDocTypesAsCertainTip)
    {
        $this->helper = $helper;
        $this->treatPhpDocTypesAsCertain = $treatPhpDocTypesAsCertain;
        $this->disable = $disable;
        $this->treatPhpDocTypesAsCertainTip = $treatPhpDocTypesAsCertainTip;
    }
    public function getNodeType() : string
    {
        return Node\Expr\Ternary::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        if ($this->disable) {
            return [];
        }
        $conditionType = $this->treatPhpDocTypesAsCertain ? $scope->getType($node->cond) : $scope->getNativeType($node->cond);
        $conditionBooleanType = $conditionType->toBoolean();
        if ($conditionBooleanType->isTrue()->yes() && $this->helper->shouldSkip($scope, $node->cond) && !$this->helper->shouldReportAlwaysTrueByDefault($node->cond)) {
            $addTip = function (RuleErrorBuilder $ruleErrorBuilder) use($scope, $node) : RuleErrorBuilder {
                if (!$this->treatPhpDocTypesAsCertain) {
                    return $ruleErrorBuilder;
                }
                $booleanNativeType = $scope->getNativeType($node->cond);
                if ($booleanNativeType instanceof ConstantBooleanType) {
                    return $ruleErrorBuilder;
                }
                if (!$this->treatPhpDocTypesAsCertainTip) {
                    return $ruleErrorBuilder;
                }
                return $ruleErrorBuilder->treatPhpDocTypesAsCertainTip();
            };
            return [$addTip(RuleErrorBuilder::message('Else branch is unreachable because ternary operator condition is always true.'))->identifier('ternary.elseUnreachable')->line($node->else->getStartLine())->build()];
        }
        return [];
    }
}
