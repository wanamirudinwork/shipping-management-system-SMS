<?php

declare (strict_types=1);
namespace PHPStan\Rules\Constants;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Php\PhpVersion;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use function sprintf;
/**
 * @implements Rule<ClassConstFetch>
 */
final class DynamicClassConstantFetchRule implements Rule
{
    /**
     * @var PhpVersion
     */
    private $phpVersion;
    /**
     * @var RuleLevelHelper
     */
    private $ruleLevelHelper;
    public function __construct(PhpVersion $phpVersion, RuleLevelHelper $ruleLevelHelper)
    {
        $this->phpVersion = $phpVersion;
        $this->ruleLevelHelper = $ruleLevelHelper;
    }
    public function getNodeType() : string
    {
        return ClassConstFetch::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        if (!$node->name instanceof Node\Expr) {
            return [];
        }
        if (!$this->phpVersion->supportsDynamicClassConstantFetch()) {
            return [RuleErrorBuilder::message('Fetching class constants with a dynamic name is supported only on PHP 8.3 and later.')->identifier('classConstant.dynamicFetch')->nonIgnorable()->build()];
        }
        $typeResult = $this->ruleLevelHelper->findTypeToCheck($scope, $node->name, '', static function (Type $type) : bool {
            return $type->isString()->yes();
        });
        $type = $typeResult->getType();
        if ($type instanceof ErrorType) {
            return [];
        }
        if ($type->isString()->yes()) {
            return [];
        }
        return [RuleErrorBuilder::message(sprintf('Class constant name in dynamic fetch can only be a string, %s given.', $type->describe(VerbosityLevel::typeOnly())))->identifier('classConstant.nameType')->build()];
    }
}
