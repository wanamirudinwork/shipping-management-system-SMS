<?php

declare (strict_types=1);
namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
/**
 * @implements Rule<Node\Stmt\Trait_>
 */
final class LocalTypeTraitAliasesRule implements Rule
{
    /**
     * @var LocalTypeAliasesCheck
     */
    private $check;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Rules\Classes\LocalTypeAliasesCheck $check, ReflectionProvider $reflectionProvider)
    {
        $this->check = $check;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getNodeType() : string
    {
        return Node\Stmt\Trait_::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        $traitName = $node->namespacedName;
        if ($traitName === null) {
            return [];
        }
        if (!$this->reflectionProvider->hasClass($traitName->toString())) {
            return [];
        }
        return $this->check->checkInTraitDefinitionContext($this->reflectionProvider->getClass($traitName->toString()));
    }
}
