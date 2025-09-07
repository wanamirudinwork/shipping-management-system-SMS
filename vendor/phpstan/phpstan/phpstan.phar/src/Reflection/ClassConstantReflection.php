<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PhpParser\Node\Expr;
use PHPStan\BetterReflection\NodeCompiler\Exception\UnableToCompileNode;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClassConstant;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;
use const NAN;
/**
 * @api
 * @final
 */
class ClassConstantReflection implements \PHPStan\Reflection\ConstantReflection
{
    /**
     * @var InitializerExprTypeResolver
     */
    private $initializerExprTypeResolver;
    /**
     * @var ClassReflection
     */
    private $declaringClass;
    /**
     * @var ReflectionClassConstant
     */
    private $reflection;
    /**
     * @var ?Type
     */
    private $nativeType;
    /**
     * @var ?Type
     */
    private $phpDocType;
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
     * @var ?Type
     */
    private $valueType = null;
    public function __construct(\PHPStan\Reflection\InitializerExprTypeResolver $initializerExprTypeResolver, \PHPStan\Reflection\ClassReflection $declaringClass, ReflectionClassConstant $reflection, ?Type $nativeType, ?Type $phpDocType, ?string $deprecatedDescription, bool $isDeprecated, bool $isInternal, bool $isFinal)
    {
        $this->initializerExprTypeResolver = $initializerExprTypeResolver;
        $this->declaringClass = $declaringClass;
        $this->reflection = $reflection;
        $this->nativeType = $nativeType;
        $this->phpDocType = $phpDocType;
        $this->deprecatedDescription = $deprecatedDescription;
        $this->isDeprecated = $isDeprecated;
        $this->isInternal = $isInternal;
        $this->isFinal = $isFinal;
    }
    public function getName() : string
    {
        return $this->reflection->getName();
    }
    public function getFileName() : ?string
    {
        return $this->declaringClass->getFileName();
    }
    /**
     * @deprecated Use getValueExpr()
     * @return mixed
     */
    public function getValue()
    {
        try {
            return $this->reflection->getValue();
        } catch (UnableToCompileNode $e) {
            return NAN;
        }
    }
    public function getValueExpr() : Expr
    {
        return $this->reflection->getValueExpression();
    }
    public function hasPhpDocType() : bool
    {
        return $this->phpDocType !== null;
    }
    public function getPhpDocType() : ?Type
    {
        return $this->phpDocType;
    }
    public function hasNativeType() : bool
    {
        return $this->nativeType !== null;
    }
    public function getNativeType() : ?Type
    {
        return $this->nativeType;
    }
    public function getValueType() : Type
    {
        if ($this->valueType === null) {
            if ($this->phpDocType !== null) {
                if ($this->nativeType !== null) {
                    return $this->valueType = TypehintHelper::decideType($this->nativeType, $this->phpDocType);
                }
                return $this->phpDocType;
            } elseif ($this->nativeType !== null) {
                return $this->nativeType;
            }
            $this->valueType = $this->initializerExprTypeResolver->getType($this->getValueExpr(), \PHPStan\Reflection\InitializerExprContext::fromClassReflection($this->declaringClass));
        }
        return $this->valueType;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->declaringClass;
    }
    public function isStatic() : bool
    {
        return \true;
    }
    public function isPrivate() : bool
    {
        return $this->reflection->isPrivate();
    }
    public function isPublic() : bool
    {
        return $this->reflection->isPublic();
    }
    public function isFinal() : bool
    {
        return $this->isFinal || $this->reflection->isFinal();
    }
    public function isDeprecated() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->isDeprecated);
    }
    public function getDeprecatedDescription() : ?string
    {
        if ($this->isDeprecated) {
            return $this->deprecatedDescription;
        }
        return null;
    }
    public function isInternal() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->isInternal);
    }
    public function getDocComment() : ?string
    {
        $docComment = $this->reflection->getDocComment();
        if ($docComment === \false) {
            return null;
        }
        return $docComment;
    }
}
