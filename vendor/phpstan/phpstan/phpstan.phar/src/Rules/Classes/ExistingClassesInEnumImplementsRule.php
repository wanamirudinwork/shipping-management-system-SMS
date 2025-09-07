<?php

declare (strict_types=1);
namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\ClassNameCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function array_map;
use function sprintf;
/**
 * @implements Rule<Node\Stmt\Enum_>
 */
final class ExistingClassesInEnumImplementsRule implements Rule
{
    /**
     * @var ClassNameCheck
     */
    private $classCheck;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ClassNameCheck $classCheck, ReflectionProvider $reflectionProvider)
    {
        $this->classCheck = $classCheck;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getNodeType() : string
    {
        return Node\Stmt\Enum_::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $messages = $this->classCheck->checkClassNames(array_map(static function (Node\Name $interfaceName) : ClassNameNodePair {
            return new ClassNameNodePair((string) $interfaceName, $interfaceName);
        }, $node->implements));
        $currentEnumName = (string) $node->namespacedName;
        foreach ($node->implements as $implements) {
            $implementedClassName = (string) $implements;
            if (!$this->reflectionProvider->hasClass($implementedClassName)) {
                if (!$scope->isInClassExists($implementedClassName)) {
                    $messages[] = RuleErrorBuilder::message(sprintf('Enum %s implements unknown interface %s.', $currentEnumName, $implementedClassName))->identifier('interface.notFound')->nonIgnorable()->discoveringSymbolsTip()->build();
                }
            } else {
                $reflection = $this->reflectionProvider->getClass($implementedClassName);
                if ($reflection->isClass()) {
                    $messages[] = RuleErrorBuilder::message(sprintf('Enum %s implements class %s.', $currentEnumName, $reflection->getDisplayName()))->identifier('enumImplements.class')->nonIgnorable()->build();
                } elseif ($reflection->isTrait()) {
                    $messages[] = RuleErrorBuilder::message(sprintf('Enum %s implements trait %s.', $currentEnumName, $reflection->getDisplayName()))->identifier('enumImplements.trait')->nonIgnorable()->build();
                } elseif ($reflection->isEnum()) {
                    $messages[] = RuleErrorBuilder::message(sprintf('Enum %s implements enum %s.', $currentEnumName, $reflection->getDisplayName()))->identifier('enumImplements.enum')->nonIgnorable()->build();
                }
            }
        }
        return $messages;
    }
}
