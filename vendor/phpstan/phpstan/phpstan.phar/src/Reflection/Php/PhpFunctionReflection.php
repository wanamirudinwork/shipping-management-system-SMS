<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Php;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionFunction;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\Cache\Cache;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\Assertions;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Reflection\ParameterReflectionWithPhpDocs;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use function array_key_exists;
use function array_map;
use function filemtime;
use function is_array;
use function is_file;
use function sprintf;
use function time;
final class PhpFunctionReflection implements FunctionReflection
{
    /**
     * @var InitializerExprTypeResolver
     */
    private $initializerExprTypeResolver;
    /**
     * @var ReflectionFunction
     */
    private $reflection;
    /**
     * @var Parser
     */
    private $parser;
    /**
     * @var FunctionCallStatementFinder
     */
    private $functionCallStatementFinder;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var TemplateTypeMap
     */
    private $templateTypeMap;
    /**
     * @var array<string, Type>
     */
    private $phpDocParameterTypes;
    /**
     * @var ?Type
     */
    private $phpDocReturnType;
    /**
     * @var ?Type
     */
    private $phpDocThrowType;
    /**
     * @var ?string
     */
    private $deprecatedDescription;
    /**
     * @var bool
     */
    private $isDeprecated;
    /**
     * @var bool
     */
    private $isInternal;
    /**
     * @var bool
     */
    private $isFinal;
    /**
     * @var ?string
     */
    private $filename;
    /**
     * @var ?bool
     */
    private $isPure;
    /**
     * @var Assertions
     */
    private $asserts;
    /**
     * @var bool
     */
    private $acceptsNamedArguments;
    /**
     * @var ?string
     */
    private $phpDocComment;
    /**
     * @var array<string, Type>
     */
    private $phpDocParameterOutTypes;
    /**
     * @var array<string, bool>
     */
    private $phpDocParameterImmediatelyInvokedCallable;
    /**
     * @var array<string, Type>
     */
    private $phpDocParameterClosureThisTypes;
    /** @var FunctionVariantWithPhpDocs[]|null */
    private $variants = null;
    /**
     * @param array<string, Type> $phpDocParameterTypes
     * @param array<string, Type> $phpDocParameterOutTypes
     * @param array<string, bool> $phpDocParameterImmediatelyInvokedCallable
     * @param array<string, Type> $phpDocParameterClosureThisTypes
     */
    public function __construct(InitializerExprTypeResolver $initializerExprTypeResolver, ReflectionFunction $reflection, Parser $parser, FunctionCallStatementFinder $functionCallStatementFinder, Cache $cache, TemplateTypeMap $templateTypeMap, array $phpDocParameterTypes, ?Type $phpDocReturnType, ?Type $phpDocThrowType, ?string $deprecatedDescription, bool $isDeprecated, bool $isInternal, bool $isFinal, ?string $filename, ?bool $isPure, Assertions $asserts, bool $acceptsNamedArguments, ?string $phpDocComment, array $phpDocParameterOutTypes, array $phpDocParameterImmediatelyInvokedCallable, array $phpDocParameterClosureThisTypes)
    {
        $this->initializerExprTypeResolver = $initializerExprTypeResolver;
        $this->reflection = $reflection;
        $this->parser = $parser;
        $this->functionCallStatementFinder = $functionCallStatementFinder;
        $this->cache = $cache;
        $this->templateTypeMap = $templateTypeMap;
        $this->phpDocParameterTypes = $phpDocParameterTypes;
        $this->phpDocReturnType = $phpDocReturnType;
        $this->phpDocThrowType = $phpDocThrowType;
        $this->deprecatedDescription = $deprecatedDescription;
        $this->isDeprecated = $isDeprecated;
        $this->isInternal = $isInternal;
        $this->isFinal = $isFinal;
        $this->filename = $filename;
        $this->isPure = $isPure;
        $this->asserts = $asserts;
        $this->acceptsNamedArguments = $acceptsNamedArguments;
        $this->phpDocComment = $phpDocComment;
        $this->phpDocParameterOutTypes = $phpDocParameterOutTypes;
        $this->phpDocParameterImmediatelyInvokedCallable = $phpDocParameterImmediatelyInvokedCallable;
        $this->phpDocParameterClosureThisTypes = $phpDocParameterClosureThisTypes;
    }
    public function getName() : string
    {
        return $this->reflection->getName();
    }
    public function getFileName() : ?string
    {
        if ($this->filename === null) {
            return null;
        }
        if (!is_file($this->filename)) {
            return null;
        }
        return $this->filename;
    }
    /**
     * @return ParametersAcceptorWithPhpDocs[]
     */
    public function getVariants() : array
    {
        if ($this->variants === null) {
            $this->variants = [new FunctionVariantWithPhpDocs($this->templateTypeMap, null, $this->getParameters(), $this->isVariadic(), $this->getReturnType(), $this->getPhpDocReturnType(), $this->getNativeReturnType())];
        }
        return $this->variants;
    }
    public function getOnlyVariant() : ParametersAcceptorWithPhpDocs
    {
        return $this->getVariants()[0];
    }
    public function getNamedArgumentsVariants() : ?array
    {
        return null;
    }
    /**
     * @return ParameterReflectionWithPhpDocs[]
     */
    private function getParameters() : array
    {
        return array_map(function (ReflectionParameter $reflection) : \PHPStan\Reflection\Php\PhpParameterReflection {
            if (array_key_exists($reflection->getName(), $this->phpDocParameterImmediatelyInvokedCallable)) {
                $immediatelyInvokedCallable = TrinaryLogic::createFromBoolean($this->phpDocParameterImmediatelyInvokedCallable[$reflection->getName()]);
            } else {
                $immediatelyInvokedCallable = TrinaryLogic::createMaybe();
            }
            return new \PHPStan\Reflection\Php\PhpParameterReflection($this->initializerExprTypeResolver, $reflection, $this->phpDocParameterTypes[$reflection->getName()] ?? null, null, $this->phpDocParameterOutTypes[$reflection->getName()] ?? null, $immediatelyInvokedCallable, $this->phpDocParameterClosureThisTypes[$reflection->getName()] ?? null);
        }, $this->reflection->getParameters());
    }
    private function isVariadic() : bool
    {
        $isNativelyVariadic = $this->reflection->isVariadic();
        if (!$isNativelyVariadic && $this->reflection->getFileName() !== \false) {
            $fileName = $this->reflection->getFileName();
            if (is_file($fileName)) {
                $functionName = $this->reflection->getName();
                $modifiedTime = filemtime($fileName);
                if ($modifiedTime === \false) {
                    $modifiedTime = time();
                }
                $variableCacheKey = sprintf('%d-v4', $modifiedTime);
                $key = sprintf('variadic-function-%s-%s', $functionName, $fileName);
                $cachedResult = $this->cache->load($key, $variableCacheKey);
                if ($cachedResult === null) {
                    $nodes = $this->parser->parseFile($fileName);
                    $result = !$this->containsVariadicFunction($nodes)->no();
                    $this->cache->save($key, $variableCacheKey, $result);
                    return $result;
                }
                return $cachedResult;
            }
        }
        return $isNativelyVariadic;
    }
    /**
     * @param Node[]|scalar[]|Node $node
     */
    private function containsVariadicFunction($node) : TrinaryLogic
    {
        $result = TrinaryLogic::createMaybe();
        if ($node instanceof Node) {
            if ($node instanceof Function_) {
                $functionName = (string) $node->namespacedName;
                if ($functionName === $this->reflection->getName()) {
                    return TrinaryLogic::createFromBoolean($this->isFunctionNodeVariadic($node));
                }
            }
            foreach ($node->getSubNodeNames() as $subNodeName) {
                $innerNode = $node->{$subNodeName};
                if (!$innerNode instanceof Node && !is_array($innerNode)) {
                    continue;
                }
                $result = $result->and($this->containsVariadicFunction($innerNode));
            }
        } elseif (is_array($node)) {
            foreach ($node as $subNode) {
                if (!$subNode instanceof Node) {
                    continue;
                }
                $result = $result->and($this->containsVariadicFunction($subNode));
            }
        }
        return $result;
    }
    private function getReturnType() : Type
    {
        return TypehintHelper::decideTypeFromReflection($this->reflection->getReturnType(), $this->phpDocReturnType);
    }
    private function getPhpDocReturnType() : Type
    {
        if ($this->phpDocReturnType !== null) {
            return $this->phpDocReturnType;
        }
        return new MixedType();
    }
    private function getNativeReturnType() : Type
    {
        return TypehintHelper::decideTypeFromReflection($this->reflection->getReturnType());
    }
    public function getDeprecatedDescription() : ?string
    {
        if ($this->isDeprecated) {
            return $this->deprecatedDescription;
        }
        return null;
    }
    public function isDeprecated() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->isDeprecated || $this->reflection->isDeprecated());
    }
    public function isInternal() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->isInternal);
    }
    public function isFinal() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->isFinal);
    }
    public function getThrowType() : ?Type
    {
        return $this->phpDocThrowType;
    }
    public function hasSideEffects() : TrinaryLogic
    {
        if ($this->getReturnType()->isVoid()->yes()) {
            return TrinaryLogic::createYes();
        }
        if ($this->isPure !== null) {
            return TrinaryLogic::createFromBoolean(!$this->isPure);
        }
        return TrinaryLogic::createMaybe();
    }
    public function isPure() : TrinaryLogic
    {
        if ($this->isPure === null) {
            return TrinaryLogic::createMaybe();
        }
        return TrinaryLogic::createFromBoolean($this->isPure);
    }
    public function isBuiltin() : bool
    {
        return $this->reflection->isInternal();
    }
    public function getAsserts() : Assertions
    {
        return $this->asserts;
    }
    public function getDocComment() : ?string
    {
        return $this->phpDocComment;
    }
    public function returnsByReference() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->reflection->returnsReference());
    }
    public function acceptsNamedArguments() : bool
    {
        return $this->acceptsNamedArguments;
    }
    private function isFunctionNodeVariadic(Function_ $node) : bool
    {
        foreach ($node->params as $parameter) {
            if ($parameter->variadic) {
                return \true;
            }
        }
        if ($this->functionCallStatementFinder->findFunctionCallInStatements(ParametersAcceptor::VARIADIC_FUNCTIONS, $node->getStmts()) !== null) {
            return \true;
        }
        return \false;
    }
}
