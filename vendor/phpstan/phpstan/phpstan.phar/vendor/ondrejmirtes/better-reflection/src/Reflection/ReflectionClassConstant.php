<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\Reflection;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use ReflectionClassConstant as CoreReflectionClassConstant;
use PHPStan\BetterReflection\BetterReflection;
use PHPStan\BetterReflection\NodeCompiler\CompiledValue;
use PHPStan\BetterReflection\NodeCompiler\CompileNodeToValue;
use PHPStan\BetterReflection\NodeCompiler\CompilerContext;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClassConstant as ReflectionClassConstantAdapter;
use PHPStan\BetterReflection\Reflection\Annotation\AnnotationHelper;
use PHPStan\BetterReflection\Reflection\Attribute\ReflectionAttributeHelper;
use PHPStan\BetterReflection\Reflection\StringCast\ReflectionClassConstantStringCast;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\Util\CalculateReflectionColumn;
use PHPStan\BetterReflection\Util\GetLastDocComment;
use function array_map;
use function assert;
/** @psalm-immutable */
class ReflectionClassConstant
{
    /** @var non-empty-string */
    private $name;
    /** @var int-mask-of<ReflectionClassConstantAdapter::IS_*> */
    private $modifiers;
    /**
     * @var \PHPStan\BetterReflection\Reflection\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\ReflectionIntersectionType|null
     */
    private $type;
    /**
     * @var \PhpParser\Node\Expr
     */
    private $value;
    /** @var non-empty-string|null */
    private $docComment;
    /** @var list<ReflectionAttribute> */
    private $attributes;
    /** @var positive-int */
    private $startLine;
    /** @var positive-int */
    private $endLine;
    /** @var positive-int */
    private $startColumn;
    /** @var positive-int */
    private $endColumn;
    /** @psalm-allow-private-mutation
     * @var \PHPStan\BetterReflection\NodeCompiler\CompiledValue|null */
    private $compiledValue = null;
    /**
     * @var \PHPStan\BetterReflection\Reflector\Reflector
     */
    private $reflector;
    /**
     * @var \PHPStan\BetterReflection\Reflection\ReflectionClass
     */
    private $declaringClass;
    /**
     * @var \PHPStan\BetterReflection\Reflection\ReflectionClass
     */
    private $implementingClass;
    private function __construct(Reflector $reflector, ClassConst $node, int $positionInNode, \PHPStan\BetterReflection\Reflection\ReflectionClass $declaringClass, \PHPStan\BetterReflection\Reflection\ReflectionClass $implementingClass)
    {
        $this->reflector = $reflector;
        $this->declaringClass = $declaringClass;
        $this->implementingClass = $implementingClass;
        $name = $node->consts[$positionInNode]->name->name;
        assert($name !== '');
        $this->name = $name;
        $this->modifiers = $this->computeModifiers($node);
        $this->type = $this->createType($node);
        $this->value = $node->consts[$positionInNode]->value;
        $this->docComment = GetLastDocComment::forNode($node);
        $this->attributes = ReflectionAttributeHelper::createAttributes($reflector, $this, $node->attrGroups);
        $startLine = $node->getStartLine();
        assert($startLine > 0);
        $endLine = $node->getEndLine();
        assert($endLine > 0);
        $this->startLine = $startLine;
        $this->endLine = $endLine;
        $this->startColumn = CalculateReflectionColumn::getStartColumn($declaringClass->getLocatedSource()->getSource(), $node);
        $this->endColumn = CalculateReflectionColumn::getEndColumn($declaringClass->getLocatedSource()->getSource(), $node);
    }
    /**
     * Create a reflection of a class's constant by Const Node
     *
     * @internal
     */
    public static function createFromNode(Reflector $reflector, ClassConst $node, int $positionInNode, \PHPStan\BetterReflection\Reflection\ReflectionClass $declaringClass, \PHPStan\BetterReflection\Reflection\ReflectionClass $implementingClass) : self
    {
        return new self($reflector, $node, $positionInNode, $declaringClass, $implementingClass);
    }
    /** @internal */
    public function withImplementingClass(\PHPStan\BetterReflection\Reflection\ReflectionClass $implementingClass) : self
    {
        $clone = clone $this;
        $clone->implementingClass = $implementingClass;
        $clone->attributes = array_map(static function (\PHPStan\BetterReflection\Reflection\ReflectionAttribute $attribute) use($clone) : \PHPStan\BetterReflection\Reflection\ReflectionAttribute {
            return $attribute->withOwner($clone);
        }, $this->attributes);
        $this->compiledValue = null;
        return $clone;
    }
    /**
     * Get the name of the reflection (e.g. if this is a ReflectionClass this
     * will be the class name).
     *
     * @return non-empty-string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\ReflectionIntersectionType|null
     */
    private function createType(ClassConst $node)
    {
        $type = $node->type;
        if ($type === null) {
            return null;
        }
        assert($type instanceof Node\Identifier || $type instanceof Node\Name || $type instanceof Node\NullableType || $type instanceof Node\UnionType || $type instanceof Node\IntersectionType);
        return \PHPStan\BetterReflection\Reflection\ReflectionType::createFromNode($this->reflector, $this, $type);
    }
    /**
     * @return \PHPStan\BetterReflection\Reflection\ReflectionNamedType|\PHPStan\BetterReflection\Reflection\ReflectionUnionType|\PHPStan\BetterReflection\Reflection\ReflectionIntersectionType|null
     */
    public function getType()
    {
        return $this->type;
    }
    public function hasType() : bool
    {
        return $this->type !== null;
    }
    /**
     * @deprecated Use getValueExpression
     */
    public function getValueExpr() : Node\Expr
    {
        return $this->getValueExpression();
    }
    public function getValueExpression() : Node\Expr
    {
        return $this->value;
    }
    /**
     * Returns constant value
     *
     * @deprecated Use getValueExpression()
     * @return mixed
     */
    public function getValue()
    {
        if ($this->compiledValue === null) {
            $this->compiledValue = (new CompileNodeToValue())->__invoke($this->value, new CompilerContext($this->reflector, $this));
        }
        return $this->compiledValue->value;
    }
    /**
     * Constant is public
     */
    public function isPublic() : bool
    {
        return ($this->modifiers & ReflectionClassConstantAdapter::IS_PUBLIC_COMPATIBILITY) === ReflectionClassConstantAdapter::IS_PUBLIC_COMPATIBILITY;
    }
    /**
     * Constant is private
     */
    public function isPrivate() : bool
    {
        // Private constant cannot be final
        return $this->modifiers === ReflectionClassConstantAdapter::IS_PRIVATE_COMPATIBILITY;
    }
    /**
     * Constant is protected
     */
    public function isProtected() : bool
    {
        return ($this->modifiers & ReflectionClassConstantAdapter::IS_PROTECTED_COMPATIBILITY) === ReflectionClassConstantAdapter::IS_PROTECTED_COMPATIBILITY;
    }
    public function isFinal() : bool
    {
        $final = ($this->modifiers & ReflectionClassConstantAdapter::IS_FINAL_COMPATIBILITY) === ReflectionClassConstantAdapter::IS_FINAL_COMPATIBILITY;
        if ($final) {
            return \true;
        }
        if (BetterReflection::$phpVersion >= 80100) {
            return \false;
        }
        return $this->getDeclaringClass()->isInterface();
    }
    /**
     * Returns a bitfield of the access modifiers for this constant
     *
     * @return int-mask-of<ReflectionClassConstantAdapter::IS_*>
     */
    public function getModifiers() : int
    {
        return $this->modifiers;
    }
    /**
     * Get the line number that this constant starts on.
     *
     * @return positive-int
     */
    public function getStartLine() : int
    {
        return $this->startLine;
    }
    /**
     * Get the line number that this constant ends on.
     *
     * @return positive-int
     */
    public function getEndLine() : int
    {
        return $this->endLine;
    }
    /** @return positive-int */
    public function getStartColumn() : int
    {
        return $this->startColumn;
    }
    /** @return positive-int */
    public function getEndColumn() : int
    {
        return $this->endColumn;
    }
    /**
     * Get the declaring class
     */
    public function getDeclaringClass() : \PHPStan\BetterReflection\Reflection\ReflectionClass
    {
        return $this->declaringClass;
    }
    /**
     * Get the class that implemented the method based on trait use.
     */
    public function getImplementingClass() : \PHPStan\BetterReflection\Reflection\ReflectionClass
    {
        return $this->implementingClass;
    }
    /** @return non-empty-string|null */
    public function getDocComment() : ?string
    {
        return $this->docComment;
    }
    public function isDeprecated() : bool
    {
        return AnnotationHelper::isDeprecated($this->getDocComment());
    }
    /** @return non-empty-string */
    public function __toString() : string
    {
        return ReflectionClassConstantStringCast::toString($this);
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
    /** @return int-mask-of<ReflectionClassConstantAdapter::IS_*> */
    private function computeModifiers(ClassConst $node) : int
    {
        $modifiers = $node->isFinal() ? ReflectionClassConstantAdapter::IS_FINAL_COMPATIBILITY : 0;
        $modifiers += $node->isPrivate() ? ReflectionClassConstantAdapter::IS_PRIVATE_COMPATIBILITY : 0;
        $modifiers += $node->isProtected() ? ReflectionClassConstantAdapter::IS_PROTECTED_COMPATIBILITY : 0;
        $modifiers += $node->isPublic() ? ReflectionClassConstantAdapter::IS_PUBLIC_COMPATIBILITY : 0;
        return $modifiers;
    }
}
