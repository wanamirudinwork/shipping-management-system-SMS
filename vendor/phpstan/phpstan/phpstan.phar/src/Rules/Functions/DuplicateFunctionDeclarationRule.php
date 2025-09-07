<?php

declare (strict_types=1);
namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\ReflectionFunction;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\File\RelativePathHelper;
use PHPStan\Node\InFunctionNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function array_map;
use function count;
use function implode;
use function sprintf;
/**
 * @implements Rule<InFunctionNode>
 */
final class DuplicateFunctionDeclarationRule implements Rule
{
    /**
     * @var Reflector
     */
    private $reflector;
    /**
     * @var RelativePathHelper
     */
    private $relativePathHelper;
    public function __construct(Reflector $reflector, RelativePathHelper $relativePathHelper)
    {
        $this->reflector = $reflector;
        $this->relativePathHelper = $relativePathHelper;
    }
    public function getNodeType() : string
    {
        return InFunctionNode::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $thisFunction = $node->getFunctionReflection();
        $allFunctions = $this->reflector->reflectAllFunctions();
        $filteredFunctions = [];
        foreach ($allFunctions as $reflectionFunction) {
            if ($reflectionFunction->getName() !== $thisFunction->getName()) {
                continue;
            }
            $filteredFunctions[] = $reflectionFunction;
        }
        if (count($filteredFunctions) < 2) {
            return [];
        }
        return [RuleErrorBuilder::message(sprintf("Function %s declared multiple times:\n%s", $thisFunction->getName(), implode("\n", array_map(function (ReflectionFunction $function) {
            return sprintf('- %s:%d', $this->relativePathHelper->getRelativePath($function->getFileName() ?? 'unknown'), $function->getStartLine());
        }, $filteredFunctions))))->identifier('function.duplicate')->build()];
    }
}
