<?php

declare (strict_types=1);
namespace PHPStan\Rules\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Internal\SprintfHelper;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\ClassNameCheck;
use PHPStan\Rules\ClassNameNodePair;
use PHPStan\Rules\Generics\GenericObjectTypeCheck;
use PHPStan\Rules\MissingTypehintCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\VerbosityLevel;
use function array_map;
use function array_merge;
use function is_string;
use function sprintf;
/**
 * @implements Rule<Node\Stmt>
 */
final class InvalidPhpDocVarTagTypeRule implements Rule
{
    /**
     * @var FileTypeMapper
     */
    private $fileTypeMapper;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var ClassNameCheck
     */
    private $classCheck;
    /**
     * @var GenericObjectTypeCheck
     */
    private $genericObjectTypeCheck;
    /**
     * @var MissingTypehintCheck
     */
    private $missingTypehintCheck;
    /**
     * @var UnresolvableTypeHelper
     */
    private $unresolvableTypeHelper;
    /**
     * @var bool
     */
    private $checkClassCaseSensitivity;
    /**
     * @var bool
     */
    private $checkMissingVarTagTypehint;
    public function __construct(FileTypeMapper $fileTypeMapper, ReflectionProvider $reflectionProvider, ClassNameCheck $classCheck, GenericObjectTypeCheck $genericObjectTypeCheck, MissingTypehintCheck $missingTypehintCheck, \PHPStan\Rules\PhpDoc\UnresolvableTypeHelper $unresolvableTypeHelper, bool $checkClassCaseSensitivity, bool $checkMissingVarTagTypehint)
    {
        $this->fileTypeMapper = $fileTypeMapper;
        $this->reflectionProvider = $reflectionProvider;
        $this->classCheck = $classCheck;
        $this->genericObjectTypeCheck = $genericObjectTypeCheck;
        $this->missingTypehintCheck = $missingTypehintCheck;
        $this->unresolvableTypeHelper = $unresolvableTypeHelper;
        $this->checkClassCaseSensitivity = $checkClassCaseSensitivity;
        $this->checkMissingVarTagTypehint = $checkMissingVarTagTypehint;
    }
    public function getNodeType() : string
    {
        return Node\Stmt::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        if ($node instanceof Node\Stmt\Property || $node instanceof Node\Stmt\PropertyProperty || $node instanceof Node\Stmt\ClassConst || $node instanceof Node\Stmt\Const_) {
            return [];
        }
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [];
        }
        $function = $scope->getFunction();
        $resolvedPhpDoc = $this->fileTypeMapper->getResolvedPhpDoc($scope->getFile(), $scope->isInClass() ? $scope->getClassReflection()->getName() : null, $scope->isInTrait() ? $scope->getTraitReflection()->getName() : null, $function !== null ? $function->getName() : null, $docComment->getText());
        $errors = [];
        foreach ($resolvedPhpDoc->getVarTags() as $name => $varTag) {
            $varTagType = $varTag->getType();
            $identifier = 'PHPDoc tag @var';
            if (is_string($name)) {
                $identifier .= sprintf(' for variable $%s', $name);
            }
            if ($this->unresolvableTypeHelper->containsUnresolvableType($varTagType)) {
                $errors[] = RuleErrorBuilder::message(sprintf('%s contains unresolvable type.', $identifier))->line($docComment->getStartLine())->identifier('varTag.unresolvableType')->build();
                continue;
            }
            if ($this->checkMissingVarTagTypehint) {
                foreach ($this->missingTypehintCheck->getIterableTypesWithMissingValueTypehint($varTagType) as $iterableType) {
                    $iterableTypeDescription = $iterableType->describe(VerbosityLevel::typeOnly());
                    $errors[] = RuleErrorBuilder::message(sprintf('%s has no value type specified in iterable type %s.', $identifier, $iterableTypeDescription))->tip(MissingTypehintCheck::MISSING_ITERABLE_VALUE_TYPE_TIP)->identifier('missingType.iterableValue')->build();
                }
            }
            $escapedIdentifier = SprintfHelper::escapeFormatString($identifier);
            $errors = array_merge($errors, $this->genericObjectTypeCheck->check($varTagType, sprintf('%s contains generic type %%s but %%s %%s is not generic.', $escapedIdentifier), sprintf('Generic type %%s in %s does not specify all template types of %%s %%s: %%s', $escapedIdentifier), sprintf('Generic type %%s in %s specifies %%d template types, but %%s %%s supports only %%d: %%s', $escapedIdentifier), sprintf('Type %%s in generic type %%s in %s is not subtype of template type %%s of %%s %%s.', $escapedIdentifier), sprintf('Call-site variance of %%s in generic type %%s in %s is in conflict with %%s template type %%s of %%s %%s.', $escapedIdentifier), sprintf('Call-site variance of %%s in generic type %%s in %s is redundant, template type %%s of %%s %%s has the same variance.', $escapedIdentifier)));
            foreach ($this->missingTypehintCheck->getNonGenericObjectTypesWithGenericClass($varTagType) as [$innerName, $genericTypeNames]) {
                $errors[] = RuleErrorBuilder::message(sprintf('%s contains generic %s but does not specify its types: %s', $identifier, $innerName, $genericTypeNames))->identifier('missingType.generics')->build();
            }
            $referencedClasses = $varTagType->getReferencedClasses();
            foreach ($referencedClasses as $referencedClass) {
                if ($this->reflectionProvider->hasClass($referencedClass)) {
                    if ($this->reflectionProvider->getClass($referencedClass)->isTrait()) {
                        $errors[] = RuleErrorBuilder::message(sprintf(sprintf('%s has invalid type %%s.', $identifier), $referencedClass))->identifier('varTag.trait')->build();
                    }
                    continue;
                }
                if ($scope->isInClassExists($referencedClass)) {
                    continue;
                }
                $errors[] = RuleErrorBuilder::message(sprintf(sprintf('%s contains unknown class %%s.', $identifier), $referencedClass))->identifier('class.notFound')->discoveringSymbolsTip()->build();
            }
            $errors = array_merge($errors, $this->classCheck->checkClassNames(array_map(static function (string $class) use($node) : ClassNameNodePair {
                return new ClassNameNodePair($class, $node);
            }, $referencedClasses), $this->checkClassCaseSensitivity));
        }
        return $errors;
    }
}
