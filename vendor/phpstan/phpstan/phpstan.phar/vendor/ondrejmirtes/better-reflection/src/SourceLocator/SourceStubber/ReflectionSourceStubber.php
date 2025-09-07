<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\SourceStubber;

use BackedEnum;
use _PHPStan_14faee166\c;
use LogicException;
use PhpParser\Builder\Class_;
use PhpParser\Builder\ClassConst;
use PhpParser\Builder\Enum_;
use PhpParser\Builder\EnumCase;
use PhpParser\Builder\Function_;
use PhpParser\Builder\FunctionLike;
use PhpParser\Builder\Interface_;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\Builder\Trait_;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass as CoreReflectionClass;
use ReflectionClassConstant as CoreReflectionClassConstant;
use ReflectionEnum as CoreReflectionEnum;
use ReflectionEnumBackedCase as CoreReflectionEnumBackedCase;
use ReflectionEnumUnitCase as CoreReflectionEnumUnitCase;
use ReflectionFunction as CoreReflectionFunction;
use ReflectionFunctionAbstract as CoreReflectionFunctionAbstract;
use ReflectionIntersectionType as CoreReflectionIntersectionType;
use ReflectionMethod as CoreReflectionMethod;
use ReflectionNamedType as CoreReflectionNamedType;
use ReflectionParameter as CoreReflectionParameter;
use ReflectionProperty as CoreReflectionProperty;
use ReflectionType as CoreReflectionType;
use ReflectionUnionType as CoreReflectionUnionType;
use PHPStan\BetterReflection\Reflection\Annotation\AnnotationHelper;
use PHPStan\BetterReflection\Util\ClassExistenceChecker;
use UnitEnum;
use function array_diff;
use function array_key_exists;
use function array_map;
use function assert;
use function explode;
use function function_exists;
use function get_class;
use function get_defined_constants;
use function implode;
use function in_array;
use function is_file;
use function is_object;
use function is_resource;
use function is_string;
use function method_exists;
use function preg_replace;
use function sprintf;
use const PHP_VERSION_ID;
/**
 * It generates a stub source from internal reflection for given class or function name.
 *
 * @internal
 */
final class ReflectionSourceStubber implements \PHPStan\BetterReflection\SourceLocator\SourceStubber\SourceStubber
{
    /**
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    /**
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $prettyPrinter;
    /**
     * @var int
     */
    private $phpVersion = PHP_VERSION_ID;
    public function __construct(Standard $prettyPrinter, int $phpVersion = PHP_VERSION_ID)
    {
        $this->phpVersion = $phpVersion;
        $this->builderFactory = new BuilderFactory();
        $this->prettyPrinter = $prettyPrinter;
    }
    /** @param class-string|trait-string $className */
    public function generateClassStub(string $className) : ?\PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData
    {
        if (!ClassExistenceChecker::exists($className, \false)) {
            return null;
        }
        $enumExists = function (string $enum, bool $autoload = \true) : bool {
            if (function_exists('enum_exists')) {
                return \enum_exists($enum, $autoload);
            }
            return $autoload && \class_exists($enum) && \false;
        };
        /** phpcs:disable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName */
        $isEnum = function_exists('enum_exists') && $enumExists($className, \false);
        /** phpcs:enable */
        $classReflection = $isEnum ? new CoreReflectionEnum($className) : new CoreReflectionClass($className);
        $classNode = $this->createClass($classReflection);
        if ($classNode instanceof Class_) {
            $this->addClassModifiers($classNode, $classReflection);
        }
        if ($classNode instanceof Class_ || $classNode instanceof Interface_ || $classNode instanceof Enum_) {
            $this->addExtendsAndImplements($classNode, $classReflection);
        }
        if ($classNode instanceof Class_ || $classNode instanceof Trait_) {
            $this->addProperties($classNode, $classReflection);
        }
        if ($classNode instanceof Class_ || $classNode instanceof Trait_ || $classNode instanceof Enum_) {
            $this->addTraitUse($classNode, $classReflection);
        }
        $this->addAttributes($classNode, $classReflection);
        $this->addDocComment($classNode, $classReflection);
        if ($classNode instanceof Enum_ && $classReflection instanceof CoreReflectionEnum) {
            $this->addEnumBackingType($classNode, $classReflection);
            $this->addEnumCases($classNode, $classReflection);
        }
        $this->addClassConstants($classNode, $classReflection);
        $this->addMethods($classNode, $classReflection);
        $node = $classNode->getNode();
        $stub = $classReflection->inNamespace() ? $this->generateStubInNamespace($node, $classReflection->getNamespaceName()) : $this->generateStub($node);
        $extensionName = ($getExtension = $classReflection->getExtension()) ? $getExtension->getName() : null;
        assert(is_string($extensionName) && $extensionName !== '' || $extensionName === null);
        return $this->createStubData($stub, $extensionName, $classReflection->getFileName() !== \false ? $classReflection->getFileName() : null);
    }
    public function generateFunctionStub(string $functionName) : ?\PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData
    {
        if (!function_exists($functionName)) {
            return null;
        }
        return $this->generateFunctionStubFromReflection(new CoreReflectionFunction($functionName));
    }
    public function generateFunctionStubFromReflection(CoreReflectionFunction $functionReflection) : ?\PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData
    {
        $shortName = $functionReflection->getShortName();
        if ($functionReflection->isClosure()) {
            $shortName = '{closure}';
        }
        $functionNode = $this->builderFactory->function($shortName);
        $this->addAttributes($functionNode, $functionReflection);
        $this->addDocComment($functionNode, $functionReflection);
        $this->addParameters($functionNode, $functionReflection);
        $returnType = $functionReflection->getReturnType();
        if ($returnType === null && method_exists($functionReflection, 'getTentativeReturnType')) {
            $returnType = $functionReflection->getTentativeReturnType();
        }
        if ($returnType !== null) {
            assert($returnType instanceof CoreReflectionNamedType || $returnType instanceof CoreReflectionUnionType || $returnType instanceof CoreReflectionIntersectionType);
            $functionNode->setReturnType($this->formatType($returnType));
        }
        $extensionName = ($getExtension = $functionReflection->getExtension()) ? $getExtension->getName() : null;
        assert(is_string($extensionName) && $extensionName !== '' || $extensionName === null);
        if (!$functionReflection->inNamespace() || $functionReflection->isClosure()) {
            return $this->createStubData($this->generateStub($functionNode->getNode()), $extensionName, $functionReflection->getFileName() !== \false ? $functionReflection->getFileName() : null);
        }
        return $this->createStubData($this->generateStubInNamespace($functionNode->getNode(), $functionReflection->getNamespaceName()), $extensionName, $functionReflection->getFileName() !== \false ? $functionReflection->getFileName() : null);
    }
    public function generateConstantStub(string $constantName) : ?\PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData
    {
        $constantData = $this->findConstantData($constantName);
        if ($constantData === null) {
            return null;
        }
        [$constantValue, $extensionName] = $constantData;
        if ($extensionName === null) {
            return null;
        }
        if (is_resource($constantValue)) {
            $constantValue = $this->builderFactory->funcCall('constant', [$constantName]);
        }
        $constantNode = $this->builderFactory->funcCall('define', [$constantName, $constantValue]);
        return $this->createStubData($this->generateStub($constantNode), $extensionName, null);
    }
    /** @return array{0: scalar|list<scalar>|resource|null, 1: non-empty-string|null}|null */
    private function findConstantData(string $constantName)
    {
        /** @var array<non-empty-string, array<string, scalar|list<scalar>|resource|null>> $constants */
        $constants = get_defined_constants(\true);
        foreach ($constants as $constantExtensionName => $extensionConstants) {
            if (array_key_exists($constantName, $extensionConstants)) {
                return [$extensionConstants[$constantName], $constantExtensionName !== 'user' ? $constantExtensionName : null];
            }
        }
        return null;
    }
    /**
     * @return \PhpParser\Builder\Class_|\PhpParser\Builder\Interface_|\PhpParser\Builder\Trait_|\PhpParser\Builder\Enum_
     */
    private function createClass(CoreReflectionClass $classReflection)
    {
        if ($classReflection instanceof CoreReflectionEnum) {
            return $this->builderFactory->enum($classReflection->getShortName());
        }
        if ($classReflection->isTrait()) {
            return $this->builderFactory->trait($classReflection->getShortName());
        }
        if ($classReflection->isInterface()) {
            return $this->builderFactory->interface($classReflection->getShortName());
        }
        return $this->builderFactory->class($classReflection->getShortName());
    }
    /**
     * @param \PhpParser\Builder\Class_|\PhpParser\Builder\Interface_|\PhpParser\Builder\Trait_|\PhpParser\Builder\Enum_|\PhpParser\Builder\ClassConst|\PhpParser\Builder\EnumCase|\PhpParser\Builder\Method|\PhpParser\Builder\Property|\PhpParser\Builder\Function_|\PhpParser\Builder\Param $node
     * @param CoreReflectionClass|CoreReflectionClassConstant|CoreReflectionEnumUnitCase|CoreReflectionMethod|CoreReflectionProperty|CoreReflectionFunction|CoreReflectionParameter $reflection
     */
    private function addAttributes($node, $reflection) : void
    {
        if (!method_exists($reflection, 'getAttributes')) {
            return;
        }
        $attributeReflections = $reflection->getAttributes();
        if ($attributeReflections === []) {
            return;
        }
        foreach ($attributeReflections as $attributeReflection) {
            $node->addAttribute($this->builderFactory->attribute(new FullyQualified($attributeReflection->getName()), $attributeReflection->getArguments()));
        }
    }
    /**
     * @param \PhpParser\Builder\Class_|\PhpParser\Builder\Interface_|\PhpParser\Builder\Trait_|\PhpParser\Builder\Enum_|\PhpParser\Builder\Method|\PhpParser\Builder\Property|\PhpParser\Builder\Function_ $node
     * @param CoreReflectionClass|CoreReflectionMethod|CoreReflectionProperty|CoreReflectionFunction $reflection
     */
    private function addDocComment($node, $reflection) : void
    {
        $docComment = $reflection->getDocComment() !== \false ? $reflection->getDocComment() : '';
        $annotations = [];
        if (($reflection instanceof CoreReflectionMethod || $reflection instanceof CoreReflectionFunction) && $reflection->isInternal()) {
            if ($reflection->isDeprecated()) {
                $annotations[] = '@deprecated';
            }
            if (method_exists($reflection, 'hasTentativeReturnType') && $reflection->hasTentativeReturnType()) {
                $annotations[] = sprintf('@%s', AnnotationHelper::TENTATIVE_RETURN_TYPE_ANNOTATION);
            }
        }
        if ($docComment === '' && $annotations === []) {
            return;
        }
        if ($docComment === '') {
            $docComment = sprintf("/**\n* %s\n*/", implode("\n *", $annotations));
        } elseif ($annotations !== []) {
            $docComment = preg_replace('~\\s+\\*/$~', sprintf("\n* %s\n*/", implode("\n *", $annotations)), $docComment);
        }
        $node->setDocComment(new Doc($docComment));
    }
    private function addEnumBackingType(Enum_ $enumNode, CoreReflectionEnum $enumReflection) : void
    {
        if (!$enumReflection->isBacked()) {
            return;
        }
        $backingType = $enumReflection->getBackingType();
        assert($backingType instanceof CoreReflectionNamedType);
        $enumNode->setScalarType($backingType->getName());
    }
    private function addClassModifiers(Class_ $classNode, CoreReflectionClass $classReflection) : void
    {
        if (!$classReflection->isInterface() && $classReflection->isAbstract()) {
            // Interface \Iterator is interface and abstract
            $classNode->makeAbstract();
        }
        if (!$classReflection->isFinal()) {
            return;
        }
        $classNode->makeFinal();
    }
    /**
     * @param \PhpParser\Builder\Class_|\PhpParser\Builder\Interface_|\PhpParser\Builder\Enum_ $classNode
     */
    private function addExtendsAndImplements($classNode, CoreReflectionClass $classReflection) : void
    {
        $interfaces = $classReflection->getInterfaceNames();
        if ($classNode instanceof Class_ || $classNode instanceof Interface_) {
            $parentClass = $classReflection->getParentClass();
            if ($parentClass !== \false) {
                $classNode->extend(new FullyQualified($parentClass->getName()));
                $interfaces = array_diff($interfaces, $parentClass->getInterfaceNames());
            }
        }
        foreach ($classReflection->getInterfaces() as $interface) {
            $interfaces = array_diff($interfaces, $interface->getInterfaceNames());
        }
        foreach ($interfaces as $interfaceName) {
            if (method_exists($classReflection, 'isEnum') && $classReflection->isEnum() && in_array($interfaceName, [BackedEnum::class, UnitEnum::class], \true)) {
                continue;
            }
            $interfaceNode = new FullyQualified($interfaceName);
            if ($classNode instanceof Interface_) {
                $classNode->extend($interfaceNode);
            } else {
                $classNode->implement($interfaceNode);
            }
        }
    }
    /**
     * @param \PhpParser\Builder\Class_|\PhpParser\Builder\Trait_|\PhpParser\Builder\Enum_ $classNode
     */
    private function addTraitUse($classNode, CoreReflectionClass $classReflection) : void
    {
        /** @var array<string, string> $traitAliases */
        $traitAliases = $classReflection->getTraitAliases();
        $traitUseAdaptations = [];
        foreach ($traitAliases as $methodNameAlias => $methodInfo) {
            [$traitName, $methodName] = explode('::', $methodInfo);
            $traitUseAdaptation = $this->builderFactory->traitUseAdaptation(new FullyQualified($traitName), $methodName);
            $traitUseAdaptation->as($methodNameAlias);
            $traitUseAdaptations[$traitName] = $traitUseAdaptation;
        }
        foreach ($classReflection->getTraitNames() as $traitName) {
            $traitUse = $this->builderFactory->useTrait(new FullyQualified($traitName));
            if (array_key_exists($traitName, $traitUseAdaptations)) {
                $traitUse->with($traitUseAdaptations[$traitName]);
            }
            $classNode->addStmt($traitUse);
        }
    }
    /**
     * @param \PhpParser\Builder\Class_|\PhpParser\Builder\Trait_ $classNode
     */
    private function addProperties($classNode, CoreReflectionClass $classReflection) : void
    {
        foreach ($classReflection->getProperties() as $propertyReflection) {
            if (!$this->isPropertyDeclaredInClass($propertyReflection, $classReflection)) {
                continue;
            }
            $propertyNode = $this->builderFactory->property($propertyReflection->getName());
            $this->addPropertyModifiers($propertyNode, $propertyReflection);
            $this->addAttributes($propertyNode, $propertyReflection);
            $this->addDocComment($propertyNode, $propertyReflection);
            if (method_exists($propertyReflection, 'hasDefaultValue') && $propertyReflection->hasDefaultValue()) {
                try {
                    $propertyNode->setDefault($propertyReflection->getDeclaringClass()->getDefaultProperties()[$propertyReflection->getName()] ?? null);
                } catch (LogicException $exception) {
                    // Nothing
                }
            }
            if ($this->phpVersion >= 70400) {
                $propertyType = method_exists($propertyReflection, 'getType') ? method_exists($propertyReflection, 'getType') ? $propertyReflection->getType() : null : null;
                if ($propertyType !== null) {
                    assert($propertyType instanceof CoreReflectionNamedType || $propertyType instanceof CoreReflectionUnionType || $propertyType instanceof CoreReflectionIntersectionType);
                    $propertyNode->setType($this->formatType($propertyType));
                }
            }
            $classNode->addStmt($propertyNode);
        }
    }
    private function isPropertyDeclaredInClass(CoreReflectionProperty $propertyReflection, CoreReflectionClass $classReflection) : bool
    {
        if ($propertyReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
            return \false;
        }
        foreach ($classReflection->getTraits() as $trait) {
            if ($trait->hasProperty($propertyReflection->getName())) {
                return \false;
            }
        }
        return \true;
    }
    private function addPropertyModifiers(Property $propertyNode, CoreReflectionProperty $propertyReflection) : void
    {
        if ($this->phpVersion >= 80100) {
            if (method_exists($propertyReflection, 'isReadOnly') && $propertyReflection->isReadOnly()) {
                $propertyNode->makeReadonly();
            }
        }
        if ($propertyReflection->isStatic()) {
            $propertyNode->makeStatic();
        }
        if ($propertyReflection->isPublic()) {
            $propertyNode->makePublic();
        } elseif ($propertyReflection->isProtected()) {
            $propertyNode->makeProtected();
        } else {
            $propertyNode->makePrivate();
        }
    }
    private function addEnumCases(Enum_ $enumNode, CoreReflectionEnum $enumReflection) : void
    {
        foreach ($enumReflection->getCases() as $enumCaseReflection) {
            $enumCaseNode = $this->builderFactory->enumCase($enumCaseReflection->getName());
            $this->addAttributes($enumCaseNode, $enumCaseReflection);
            if ($enumCaseReflection instanceof CoreReflectionEnumBackedCase) {
                $enumCaseNode->setValue($enumCaseReflection->getBackingValue());
            }
            $enumNode->addStmt($enumCaseNode);
        }
    }
    /**
     * @param \PhpParser\Builder\Class_|\PhpParser\Builder\Interface_|\PhpParser\Builder\Trait_|\PhpParser\Builder\Enum_ $classNode
     */
    private function addClassConstants($classNode, CoreReflectionClass $classReflection) : void
    {
        foreach ($classReflection->getReflectionConstants() as $constantReflection) {
            if (method_exists($constantReflection, 'isEnumCase') && $constantReflection->isEnumCase()) {
                continue;
            }
            if ($constantReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
                continue;
            }
            $classConstantNode = $this->builderFactory->classConst($constantReflection->getName(), $constantReflection->getValue());
            if (method_exists($constantReflection, 'getType')) {
                $constantType = $constantReflection->getType();
                if ($constantType !== null) {
                    assert($constantType instanceof CoreReflectionNamedType || $constantType instanceof CoreReflectionUnionType || $constantType instanceof CoreReflectionIntersectionType);
                    $classConstantNode->setType($this->formatType($constantType));
                }
            }
            $this->addAttributes($classConstantNode, $constantReflection);
            if ($constantReflection->getDocComment() !== \false) {
                $classConstantNode->setDocComment(new Doc($constantReflection->getDocComment()));
            }
            $this->addClassConstantModifiers($classConstantNode, $constantReflection);
            $classNode->addStmt($classConstantNode);
        }
    }
    private function addClassConstantModifiers(ClassConst $classConstantNode, CoreReflectionClassConstant $classConstantReflection) : void
    {
        if (method_exists($classConstantReflection, 'isFinal') && $classConstantReflection->isFinal()) {
            $classConstantNode->makeFinal();
        }
        if ($classConstantReflection->isPrivate()) {
            $classConstantNode->makePrivate();
        } elseif ($classConstantReflection->isProtected()) {
            $classConstantNode->makeProtected();
        } else {
            $classConstantNode->makePublic();
        }
    }
    /**
     * @param \PhpParser\Builder\Class_|\PhpParser\Builder\Interface_|\PhpParser\Builder\Trait_|\PhpParser\Builder\Enum_ $classNode
     */
    private function addMethods($classNode, CoreReflectionClass $classReflection) : void
    {
        foreach ($classReflection->getMethods() as $methodReflection) {
            if (!$this->isMethodDeclaredInClass($methodReflection, $classReflection)) {
                continue;
            }
            $methodNode = $this->builderFactory->method($methodReflection->getName());
            $this->addMethodFlags($methodNode, $methodReflection);
            $this->addAttributes($methodNode, $methodReflection);
            $this->addDocComment($methodNode, $methodReflection);
            $this->addParameters($methodNode, $methodReflection);
            $returnType = $methodReflection->getReturnType();
            if ($returnType === null && method_exists($methodReflection, 'getTentativeReturnType')) {
                $returnType = $methodReflection->getTentativeReturnType();
            }
            if ($returnType !== null) {
                assert($returnType instanceof CoreReflectionNamedType || $returnType instanceof CoreReflectionUnionType || $returnType instanceof CoreReflectionIntersectionType);
                $methodNode->setReturnType($this->formatType($returnType));
            }
            $classNode->addStmt($methodNode);
        }
    }
    private function isMethodDeclaredInClass(CoreReflectionMethod $methodReflection, CoreReflectionClass $classReflection) : bool
    {
        if ($methodReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
            return \false;
        }
        $methodName = $methodReflection->getName();
        /** @var array<string, string> $traitAliases */
        $traitAliases = $classReflection->getTraitAliases();
        if (array_key_exists($methodName, $traitAliases)) {
            return \false;
        }
        foreach ($classReflection->getTraits() as $trait) {
            if ($trait->hasMethod($methodName)) {
                return \false;
            }
        }
        if ($classReflection instanceof CoreReflectionEnum) {
            if ($methodName === 'cases') {
                return \false;
            }
            if ($classReflection->isBacked() && in_array($methodName, ['from', 'tryFrom'], \true)) {
                return \false;
            }
        }
        return \true;
    }
    private function addMethodFlags(Method $methodNode, CoreReflectionMethod $methodReflection) : void
    {
        if ($methodReflection->isFinal()) {
            $methodNode->makeFinal();
        }
        if ($methodReflection->isAbstract() && !$methodReflection->getDeclaringClass()->isInterface()) {
            $methodNode->makeAbstract();
        }
        if ($methodReflection->isStatic()) {
            $methodNode->makeStatic();
        }
        if ($methodReflection->isPublic()) {
            $methodNode->makePublic();
        }
        if ($methodReflection->isProtected()) {
            $methodNode->makeProtected();
        }
        if ($methodReflection->isPrivate()) {
            $methodNode->makePrivate();
        }
        if (!$methodReflection->returnsReference()) {
            return;
        }
        $methodNode->makeReturnByRef();
    }
    private function addParameters(FunctionLike $functionNode, CoreReflectionFunctionAbstract $functionReflectionAbstract) : void
    {
        foreach ($functionReflectionAbstract->getParameters() as $parameterReflection) {
            $parameterNode = $this->builderFactory->param($parameterReflection->getName());
            $this->addParameterModifiers($parameterReflection, $parameterNode);
            $this->setParameterDefaultValue($parameterReflection, $parameterNode);
            $this->addAttributes($parameterNode, $parameterReflection);
            $functionNode->addParam($parameterNode);
        }
    }
    private function addParameterModifiers(CoreReflectionParameter $parameterReflection, Param $parameterNode) : void
    {
        if ($parameterReflection->isVariadic()) {
            $parameterNode->makeVariadic();
        }
        if ($parameterReflection->isPassedByReference()) {
            $parameterNode->makeByRef();
        }
        $parameterType = $parameterReflection->getType();
        if ($parameterType === null) {
            return;
        }
        assert($parameterType instanceof CoreReflectionNamedType || $parameterType instanceof CoreReflectionUnionType || $parameterType instanceof CoreReflectionIntersectionType);
        $parameterNode->setType($this->formatType($parameterType));
    }
    private function setParameterDefaultValue(CoreReflectionParameter $parameterReflection, Param $parameterNode) : void
    {
        if (!$parameterReflection->isOptional()) {
            return;
        }
        if ($parameterReflection->isVariadic()) {
            return;
        }
        if (!$parameterReflection->isDefaultValueAvailable()) {
            if ($parameterReflection->allowsNull()) {
                $parameterNode->setDefault(null);
            } else {
                $parameterNode->setDefault(new Node\Expr\ConstFetch(new FullyQualified('UNKNOWN')));
            }
            return;
        }
        $defaultValue = $parameterReflection->getDefaultValue();
        if (is_object($defaultValue)) {
            $className = get_class($defaultValue);
            $enumExists = function (string $enum, bool $autoload = \true) : bool {
                if (function_exists('enum_exists')) {
                    return \enum_exists($enum, $autoload);
                }
                return $autoload && \class_exists($enum) && \false;
            };
            $isEnum = function_exists('enum_exists') && $enumExists($className, \false);
            if ($isEnum) {
                $parameterNode->setDefault(new Node\Expr\ClassConstFetch(new FullyQualified($className), new Node\Identifier($defaultValue->name)));
                return;
            }
            if ($this->phpVersion >= 80100) {
                $parameterNode->setDefault(new Node\Expr\New_(new FullyQualified($className)));
            } else {
                $parameterNode->setDefault(new Node\Expr\ConstFetch(new Name('null')));
            }
            return;
        }
        $parameterNode->setDefault($defaultValue);
    }
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\IntersectionType
     */
    private function formatType(CoreReflectionType $type)
    {
        if ($type instanceof CoreReflectionIntersectionType) {
            /** @var list<Name> $types */
            $types = $this->formatTypes($type->getTypes());
            if ($this->phpVersion >= 80100) {
                return new IntersectionType($types);
            }
            return $types[0];
        }
        if ($type instanceof CoreReflectionUnionType) {
            /** @var list<Name|IntersectionType> $types */
            $types = $this->formatTypes($type->getTypes());
            if ($this->phpVersion >= 80200) {
                return new UnionType($types);
            }
            if ($this->phpVersion < 80000) {
                return $types[0];
            }
            $intersectionTypes = [];
            $otherNames = [];
            foreach ($types as $type) {
                if ($type instanceof IntersectionType) {
                    $intersectionTypes[] = $type;
                    continue;
                }
                $otherNames[] = $type;
            }
            if ($this->phpVersion >= 80100) {
                if (\count($intersectionTypes) > 0) {
                    return $intersectionTypes[0];
                }
            }
            if (\count($otherNames) > 1) {
                return new UnionType($otherNames);
            }
            if (\count($otherNames) > 0) {
                return $otherNames[0];
            }
            return new Name('null');
        }
        assert($type instanceof CoreReflectionNamedType);
        $name = $type->getName();
        $nameNode = $this->formatNamedType($type);
        if (!$type->allowsNull() || $name === 'mixed' || $name === 'null') {
            return $nameNode;
        }
        return new NullableType($nameNode);
    }
    /**
     * @param list<CoreReflectionType> $types
     *
     * @return list<Name|UnionType|IntersectionType>
     */
    private function formatTypes(array $types) : array
    {
        return array_map(function (CoreReflectionType $type) {
            $formattedType = $this->formatType($type);
            assert($formattedType instanceof Name || $formattedType instanceof UnionType || $formattedType instanceof IntersectionType);
            return $formattedType;
        }, $types);
    }
    private function formatNamedType(CoreReflectionNamedType $type) : Name
    {
        $name = $type->getName();
        return $type->isBuiltin() || in_array($name, ['self', 'parent', 'static'], \true) ? new Name($name) : new FullyQualified($name);
    }
    private function generateStubInNamespace(Node $node, string $namespaceName) : string
    {
        $namespaceBuilder = $this->builderFactory->namespace($namespaceName);
        $namespaceBuilder->addStmt($node);
        return $this->generateStub($namespaceBuilder->getNode());
    }
    private function generateStub(Node $node) : string
    {
        return sprintf("<?php\n\n%s%s\n", $this->prettyPrinter->prettyPrint([$node]), $node instanceof Node\Expr\FuncCall ? ';' : '');
    }
    /** @param non-empty-string|null $extensionName */
    private function createStubData(string $stub, ?string $extensionName, ?string $fileName) : \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData
    {
        return new \PHPStan\BetterReflection\SourceLocator\SourceStubber\StubData($stub, $extensionName, $fileName);
    }
}
