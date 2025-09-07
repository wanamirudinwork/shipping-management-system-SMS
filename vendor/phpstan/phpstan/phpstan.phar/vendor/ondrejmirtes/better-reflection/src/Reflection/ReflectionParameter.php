<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use Closure;
use Exception;
use InvalidArgumentException;
use LogicException;
use OutOfBoundsException;
use PhpParser\Node;
use PhpParser\Node\Param as ParamNode;
use PHPStan\BetterReflection\NodeCompiler\CompiledValue;
use PHPStan\BetterReflection\NodeCompiler\CompileNodeToValue;
use PHPStan\BetterReflection\NodeCompiler\CompilerContext;
use PHPStan\BetterReflection\NodeCompiler\Exception\UnableToCompileNode;
use PHPStan\BetterReflection\Reflection\Attribute\ReflectionAttributeHelper;
use PHPStan\BetterReflection\Reflection\Exception\CodeLocationMissing;
use PHPStan\BetterReflection\Reflection\StringCast\ReflectionParameterStringCast;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\Util\CalculateReflectionColumn;
use PHPStan\BetterReflection\Util\Exception\NoNodePosition;
use function array_map;
use function assert;
use function count;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
/** @psalm-immutable */
class ReflectionParameter
{
    /** @var non-empty-string */
    private $name;
    /**
     * @var \PhpParser\Node\Expr|null
     */
    private $default;
    /**
     * @var \PHPStan\BetterReflection\Reflection\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\ReflectionIntersectionType|null
     */
    private $type;
    /**
     * @var bool
     */
    private $isVariadic;
    /**
     * @var bool
     */
    private $byRef;
    /**
     * @var bool
     */
    private $isPromoted;
    /** @var list<ReflectionAttribute> */
    private $attributes;
    /** @var positive-int|null */
    private $startLine;
    /** @var positive-int|null */
    private $endLine;
    /** @var positive-int|null */
    private $startColumn;
    /** @var positive-int|null */
    private $endColumn;
    /** @psalm-allow-private-mutation
     * @var \PHPStan\BetterReflection\NodeCompiler\CompiledValue|null */
    private $compiledDefaultValue = null;
    /**
     * @var \PHPStan\BetterReflection\Reflector\Reflector
     */
    private $reflector;
    /**
     * @var \PHPStan\BetterReflection\Reflection\ReflectionMethod|\PHPStan\BetterReflection\Reflection\ReflectionFunction
     */
    private $function;
    /**
     * @var int
     */
    private $parameterIndex;
    /**
     * @var bool
     */
    private $isOptional;
    /**
     * @param \PHPStan\BetterReflection\Reflection\ReflectionMethod|\PHPStan\BetterReflection\Reflection\ReflectionFunction $function
     */
    private function __construct(Reflector $reflector, ParamNode $node, $function, int $parameterIndex, bool $isOptional)
    {
        $this->reflector = $reflector;
        $this->function = $function;
        $this->parameterIndex = $parameterIndex;
        $this->isOptional = $isOptional;
        assert($node->var instanceof Node\Expr\Variable);
        assert(is_string($node->var->name));
        $name = $node->var->name;
        assert($name !== '');
        $this->name = $name;
        $this->default = $node->default;
        $this->isPromoted = $node->flags !== 0;
        $this->type = $this->createType($node);
        $this->isVariadic = $node->variadic;
        $this->byRef = $node->byRef;
        $this->attributes = ReflectionAttributeHelper::createAttributes($reflector, $this, $node->attrGroups);
        if ($node->hasAttribute('startLine')) {
            $startLine = $node->getStartLine();
            assert($startLine > 0);
        } else {
            $startLine = null;
        }
        if ($node->hasAttribute('endLine')) {
            $endLine = $node->getEndLine();
            assert($endLine > 0);
        } else {
            $endLine = null;
        }
        $this->startLine = $startLine;
        $this->endLine = $endLine;
        try {
            $this->startColumn = CalculateReflectionColumn::getStartColumn($function->getLocatedSource()->getSource(), $node);
        } catch (NoNodePosition $exception) {
            $this->startColumn = null;
        }
        try {
            $this->endColumn = CalculateReflectionColumn::getEndColumn($function->getLocatedSource()->getSource(), $node);
        } catch (NoNodePosition $exception) {
            $this->endColumn = null;
        }
    }
    /**
     * Create a reflection of a parameter using a class name
     *
     * @param non-empty-string $methodName
     * @param non-empty-string $parameterName
     *
     * @throws OutOfBoundsException
     */
    public static function createFromClassNameAndMethod(string $className, string $methodName, string $parameterName) : self
    {
        $parameter = ($getMethod = \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromName($className)->getMethod($methodName)) ? $getMethod->getParameter($parameterName) : null;
        if ($parameter === null) {
            throw new OutOfBoundsException(sprintf('Could not find parameter: %s', $parameterName));
        }
        return $parameter;
    }
    /**
     * Create a reflection of a parameter using an instance
     *
     * @param non-empty-string $methodName
     * @param non-empty-string $parameterName
     *
     * @throws OutOfBoundsException
     */
    public static function createFromClassInstanceAndMethod(object $instance, string $methodName, string $parameterName) : self
    {
        $parameter = ($getMethod = \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromInstance($instance)->getMethod($methodName)) ? $getMethod->getParameter($parameterName) : null;
        if ($parameter === null) {
            throw new OutOfBoundsException(sprintf('Could not find parameter: %s', $parameterName));
        }
        return $parameter;
    }
    /**
     * Create a reflection of a parameter using a closure
     *
     * @param non-empty-string $parameterName
     *
     * @throws OutOfBoundsException
     */
    public static function createFromClosure(Closure $closure, string $parameterName) : \PHPStan\BetterReflection\Reflection\ReflectionParameter
    {
        $parameter = \PHPStan\BetterReflection\Reflection\ReflectionFunction::createFromClosure($closure)->getParameter($parameterName);
        if ($parameter === null) {
            throw new OutOfBoundsException(sprintf('Could not find parameter: %s', $parameterName));
        }
        return $parameter;
    }
    /**
     * Create the parameter from the given spec. Possible $spec parameters are:
     *
     *  - [$instance, 'method']
     *  - ['Foo', 'bar']
     *  - ['foo']
     *  - [function () {}]
     *
     * @param object[]|string[]|string|Closure $spec
     * @param non-empty-string                 $parameterName
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public static function createFromSpec($spec, string $parameterName) : self
    {
        try {
            if (is_array($spec) && count($spec) === 2 && is_string($spec[1])) {
                assert($spec[1] !== '');
                if (is_object($spec[0])) {
                    return self::createFromClassInstanceAndMethod($spec[0], $spec[1], $parameterName);
                }
                return self::createFromClassNameAndMethod($spec[0], $spec[1], $parameterName);
            }
            if (is_string($spec)) {
                $parameter = \PHPStan\BetterReflection\Reflection\ReflectionFunction::createFromName($spec)->getParameter($parameterName);
                if ($parameter === null) {
                    throw new OutOfBoundsException(sprintf('Could not find parameter: %s', $parameterName));
                }
                return $parameter;
            }
            if ($spec instanceof Closure) {
                return self::createFromClosure($spec, $parameterName);
            }
        } catch (OutOfBoundsException $e) {
            throw new InvalidArgumentException('Could not create reflection from the spec given', 0, $e);
        }
        throw new InvalidArgumentException('Could not create reflection from the spec given');
    }
    /** @return non-empty-string */
    public function __toString() : string
    {
        return ReflectionParameterStringCast::toString($this);
    }
    /**
     * @internal
     *
     * @param ParamNode $node Node has to be processed by the PhpParser\NodeVisitor\NameResolver
     * @param \PHPStan\BetterReflection\Reflection\ReflectionMethod|\PHPStan\BetterReflection\Reflection\ReflectionFunction $function
     */
    public static function createFromNode(Reflector $reflector, ParamNode $node, $function, int $parameterIndex, bool $isOptional) : self
    {
        return new self($reflector, $node, $function, $parameterIndex, $isOptional);
    }
    /** @internal
     * @param \PHPStan\BetterReflection\Reflection\ReflectionMethod|\PHPStan\BetterReflection\Reflection\ReflectionFunction $function */
    public function withFunction($function) : self
    {
        $clone = clone $this;
        $clone->function = $function;
        if ($clone->type !== null) {
            $clone->type = $clone->type->withOwner($clone);
        }
        $clone->attributes = array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionAttribute $attribute) use($clone) : \PHPStan\BetterReflection\Reflection\ReflectionAttribute {
            return $attribute->withOwner($clone);
        }, $this->attributes);
        $this->compiledDefaultValue = null;
        return $clone;
    }
    /** @throws LogicException */
    private function getCompiledDefaultValue() : CompiledValue
    {
        if (!$this->isDefaultValueAvailable()) {
            throw new LogicException('This parameter does not have a default value available');
        }
        if ($this->compiledDefaultValue === null) {
            $this->compiledDefaultValue = (new CompileNodeToValue())->__invoke($this->default, new CompilerContext($this->reflector, $this));
        }
        return $this->compiledDefaultValue;
    }
    /**
     * Get the name of the parameter.
     *
     * @return non-empty-string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * Get the function (or method) that declared this parameter.
     * @return \PHPStan\BetterReflection\Reflection\ReflectionMethod|\PHPStan\BetterReflection\Reflection\ReflectionFunction
     */
    public function getDeclaringFunction()
    {
        return $this->function;
    }
    /**
     * Get the class from the method that this parameter belongs to, if it
     * exists.
     *
     * This will return null if the declaring function is not a method.
     */
    public function getDeclaringClass() : ?\PHPStan\BetterReflection\Reflection\ReflectionClass
    {
        if ($this->function instanceof \PHPStan\BetterReflection\Reflection\ReflectionMethod) {
            return $this->function->getDeclaringClass();
        }
        return null;
    }
    public function getImplementingClass() : ?\PHPStan\BetterReflection\Reflection\ReflectionClass
    {
        if ($this->function instanceof \PHPStan\BetterReflection\Reflection\ReflectionMethod) {
            return $this->function->getImplementingClass();
        }
        return null;
    }
    /**
     * Is the parameter optional?
     *
     * Note this is distinct from "isDefaultValueAvailable" because you can have
     * a default value, but the parameter not be optional. In the example, the
     * $foo parameter isOptional() == false, but isDefaultValueAvailable == true
     *
     * @example someMethod($foo = 'foo', $bar)
     */
    public function isOptional() : bool
    {
        return $this->isOptional;
    }
    /**
     * Does the parameter have a default, regardless of whether it is optional.
     *
     * Note this is distinct from "isOptional" because you can have
     * a default value, but the parameter not be optional. In the example, the
     * $foo parameter isOptional() == false, but isDefaultValueAvailable == true
     *
     * @example someMethod($foo = 'foo', $bar)
     * @psalm-assert-if-true Node\Expr $this->default
     */
    public function isDefaultValueAvailable() : bool
    {
        return $this->default !== null;
    }
    /**
     * @deprecated Use getDefaultValueExpression()
     */
    public function getDefaultValueExpr() : ?\PhpParser\Node\Expr
    {
        return $this->getDefaultValueExpression();
    }
    public function getDefaultValueExpression() : ?\PhpParser\Node\Expr
    {
        return $this->default;
    }
    /**
     * Get the default value of the parameter.
     *
     * @deprecated Use getDefaultValueExpression()
     *
     * @throws LogicException
     * @throws UnableToCompileNode
     * @return mixed
     */
    public function getDefaultValue()
    {
        /** @psalm-var scalar|array<scalar>|null $value */
        $value = $this->getCompiledDefaultValue()->value;
        return $value;
    }
    /**
     * Does this method allow null for a parameter?
     */
    public function allowsNull() : bool
    {
        $type = $this->getType();
        if ($type === null) {
            return \true;
        }
        return $type->allowsNull();
    }
    /**
     * Find the position of the parameter, left to right, starting at zero.
     */
    public function getPosition() : int
    {
        return $this->parameterIndex;
    }
    /**
     * Get the ReflectionType instance representing the type declaration for
     * this parameter
     *
     * (note: this has nothing to do with DocBlocks).
     * @return \PHPStan\BetterReflection\Reflection\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\ReflectionIntersectionType|null
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\ReflectionIntersectionType|null
     */
    private function createType(ParamNode $node)
    {
        $type = $node->type;
        if ($type === null) {
            return null;
        }
        assert($type instanceof Node\Identifier || $type instanceof Node\Name || $type instanceof Node\NullableType || $type instanceof Node\UnionType || $type instanceof Node\IntersectionType);
        $allowsNull = $this->default instanceof Node\Expr\ConstFetch && $this->default->name->toLowerString() === 'null' && !$this->isPromoted;
        return \PHPStan\BetterReflection\Reflection\ReflectionType::createFromNode($this->reflector, $this, $type, $allowsNull);
    }
    /**
     * Does this parameter have a type declaration?
     *
     * (note: this has nothing to do with DocBlocks).
     */
    public function hasType() : bool
    {
        return $this->type !== null;
    }
    /**
     * Is this parameter a variadic (denoted by ...$param).
     */
    public function isVariadic() : bool
    {
        return $this->isVariadic;
    }
    /**
     * Is this parameter passed by reference (denoted by &$param).
     */
    public function isPassedByReference() : bool
    {
        return $this->byRef;
    }
    public function canBePassedByValue() : bool
    {
        return !$this->isPassedByReference();
    }
    public function isPromoted() : bool
    {
        return $this->isPromoted;
    }
    /** @throws LogicException */
    public function isDefaultValueConstant() : bool
    {
        return $this->getCompiledDefaultValue()->constantName !== null;
    }
    /** @throws LogicException */
    public function getDefaultValueConstantName() : string
    {
        $compiledDefaultValue = $this->getCompiledDefaultValue();
        if ($compiledDefaultValue->constantName === null) {
            throw new LogicException('This parameter is not a constant default value, so cannot have a constant name');
        }
        return $compiledDefaultValue->constantName;
    }
    /**
     * @return positive-int
     *
     * @throws CodeLocationMissing
     */
    public function getStartLine() : int
    {
        if ($this->startLine === null) {
            throw CodeLocationMissing::create();
        }
        return $this->startLine;
    }
    /**
     * @return positive-int
     *
     * @throws CodeLocationMissing
     */
    public function getEndLine() : int
    {
        if ($this->endLine === null) {
            throw CodeLocationMissing::create();
        }
        return $this->endLine;
    }
    /**
     * @return positive-int
     *
     * @throws CodeLocationMissing
     */
    public function getStartColumn() : int
    {
        if ($this->startColumn === null) {
            throw CodeLocationMissing::create();
        }
        return $this->startColumn;
    }
    /**
     * @return positive-int
     *
     * @throws CodeLocationMissing
     */
    public function getEndColumn() : int
    {
        if ($this->endColumn === null) {
            throw CodeLocationMissing::create();
        }
        return $this->endColumn;
    }
    /** @return list<ReflectionAttribute> */
    public function getAttributes() : array
    {
        return $this->attributes;
    }
    /** @return list<ReflectionAttribute> */
    public function getAttributesByName(string $name) : array
    {
        return ReflectionAttributeHelper::filterAttributesByName($this->getAttributes(), $name);
    }
    /**
     * @param class-string $className
     *
     * @return list<ReflectionAttribute>
     */
    public function getAttributesByInstance(string $className) : array
    {
        return ReflectionAttributeHelper::filterAttributesByInstance($this->getAttributes(), $className);
    }
}
