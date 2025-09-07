<?php

declare (strict_types=1);
namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FunctionReturnStatementsNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\TrinaryLogic;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;
use function sprintf;
/**
 * @implements Rule<FunctionReturnStatementsNode>
 */
final class ThrowsVoidFunctionWithExplicitThrowPointRule implements Rule
{
    /**
     * @var ExceptionTypeResolver
     */
    private $exceptionTypeResolver;
    /**
     * @var bool
     */
    private $missingCheckedExceptionInThrows;
    public function __construct(\PHPStan\Rules\Exceptions\ExceptionTypeResolver $exceptionTypeResolver, bool $missingCheckedExceptionInThrows)
    {
        $this->exceptionTypeResolver = $exceptionTypeResolver;
        $this->missingCheckedExceptionInThrows = $missingCheckedExceptionInThrows;
    }
    public function getNodeType() : string
    {
        return FunctionReturnStatementsNode::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $statementResult = $node->getStatementResult();
        $functionReflection = $node->getFunctionReflection();
        if ($functionReflection->getThrowType() === null || !$functionReflection->getThrowType()->isVoid()->yes()) {
            return [];
        }
        $errors = [];
        foreach ($statementResult->getThrowPoints() as $throwPoint) {
            if (!$throwPoint->isExplicit()) {
                continue;
            }
            foreach (TypeUtils::flattenTypes($throwPoint->getType()) as $throwPointType) {
                $isCheckedException = TrinaryLogic::createFromBoolean($this->missingCheckedExceptionInThrows)->lazyAnd($throwPointType->getObjectClassNames(), function (string $objectClassName) use($throwPoint) {
                    return TrinaryLogic::createFromBoolean($this->exceptionTypeResolver->isCheckedException($objectClassName, $throwPoint->getScope()));
                });
                if ($isCheckedException->yes()) {
                    continue;
                }
                $errors[] = RuleErrorBuilder::message(sprintf('Function %s() throws exception %s but the PHPDoc contains @throws void.', $functionReflection->getName(), $throwPointType->describe(VerbosityLevel::typeOnly())))->line($throwPoint->getNode()->getStartLine())->identifier('throws.void')->build();
            }
        }
        return $errors;
    }
}
