<?php

declare (strict_types=1);
namespace PHPStan\Type;

use Closure;
use PhpParser\Parser;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionType;
use PHPStan\BetterReflection\Reflection\ReflectionParameter as BetterReflectionParameter;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Ast\FindReflectionsInTree;
use PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber;
use PHPStan\Reflection\InitializerExprContext;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\ShouldNotHappenException;
use ReflectionFunction;
use function array_map;
use function count;
use function str_replace;
/**
 * @api
 * @final
 */
class ClosureTypeFactory
{
    /**
     * @var InitializerExprTypeResolver
     */
    private $initializerExprTypeResolver;
    /**
     * @var ReflectionSourceStubber
     */
    private $reflectionSourceStubber;
    /**
     * @var Reflector
     */
    private $reflector;
    /**
     * @var Parser
     */
    private $parser;
    public function __construct(InitializerExprTypeResolver $initializerExprTypeResolver, ReflectionSourceStubber $reflectionSourceStubber, Reflector $reflector, Parser $parser)
    {
        $this->initializerExprTypeResolver = $initializerExprTypeResolver;
        $this->reflectionSourceStubber = $reflectionSourceStubber;
        $this->reflector = $reflector;
        $this->parser = $parser;
    }
    /**
     * @param Closure(): mixed $closure
     */
    public function fromClosureObject(Closure $closure) : \PHPStan\Type\ClosureType
    {
        $stubData = $this->reflectionSourceStubber->generateFunctionStubFromReflection(new ReflectionFunction($closure));
        if ($stubData === null) {
            throw new ShouldNotHappenException('Closure reflection not found.');
        }
        $source = $stubData->getStub();
        $source = str_replace('{closure}', 'foo', $source);
        $locatedSource = new LocatedSource($source, '{closure}', $stubData->getFileName());
        $find = new FindReflectionsInTree(new NodeToReflection());
        $ast = $this->parser->parse($locatedSource->getSource());
        if ($ast === null) {
            throw new ShouldNotHappenException('Closure reflection not found.');
        }
        /** @var list<\PHPStan\BetterReflection\Reflection\ReflectionFunction> $reflections */
        $reflections = $find($this->reflector, $ast, new IdentifierType(IdentifierType::IDENTIFIER_FUNCTION), $locatedSource);
        if (count($reflections) !== 1) {
            throw new ShouldNotHappenException('Closure reflection not found.');
        }
        $betterReflectionFunction = $reflections[0];
        $parameters = array_map(function (BetterReflectionParameter $parameter) {
            return new class($parameter, $this->initializerExprTypeResolver) implements ParameterReflection
            {
                /**
                 * @var BetterReflectionParameter
                 */
                private $reflection;
                /**
                 * @var InitializerExprTypeResolver
                 */
                private $initializerExprTypeResolver;
                public function __construct(BetterReflectionParameter $reflection, InitializerExprTypeResolver $initializerExprTypeResolver)
                {
                    $this->reflection = $reflection;
                    $this->initializerExprTypeResolver = $initializerExprTypeResolver;
                }
                public function getName() : string
                {
                    return $this->reflection->getName();
                }
                public function isOptional() : bool
                {
                    return $this->reflection->isOptional();
                }
                public function getType() : \PHPStan\Type\Type
                {
                    return \PHPStan\Type\TypehintHelper::decideTypeFromReflection(ReflectionType::fromTypeOrNull($this->reflection->getType()), null, null, $this->reflection->isVariadic());
                }
                public function passedByReference() : PassedByReference
                {
                    return $this->reflection->isPassedByReference() ? PassedByReference::createCreatesNewVariable() : PassedByReference::createNo();
                }
                public function isVariadic() : bool
                {
                    return $this->reflection->isVariadic();
                }
                public function getDefaultValue() : ?\PHPStan\Type\Type
                {
                    if (!$this->reflection->isDefaultValueAvailable()) {
                        return null;
                    }
                    $defaultExpr = $this->reflection->getDefaultValueExpression();
                    if ($defaultExpr === null) {
                        return null;
                    }
                    return $this->initializerExprTypeResolver->getType($defaultExpr, InitializerExprContext::fromReflectionParameter(new ReflectionParameter($this->reflection)));
                }
            };
        }, $betterReflectionFunction->getParameters());
        return new \PHPStan\Type\ClosureType($parameters, \PHPStan\Type\TypehintHelper::decideTypeFromReflection(ReflectionType::fromTypeOrNull($betterReflectionFunction->getReturnType())), $betterReflectionFunction->isVariadic());
    }
}
