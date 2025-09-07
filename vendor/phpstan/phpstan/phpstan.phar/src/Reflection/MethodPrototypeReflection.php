<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Type\Type;
final class MethodPrototypeReflection implements \PHPStan\Reflection\ClassMemberReflection
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var ClassReflection
     */
    private $declaringClass;
    /**
     * @var bool
     */
    private $isStatic;
    /**
     * @var bool
     */
    private $isPrivate;
    /**
     * @var bool
     */
    private $isPublic;
    /**
     * @var bool
     */
    private $isAbstract;
    /**
     * @var bool
     */
    private $isFinal;
    /**
     * @var bool
     */
    private $isInternal;
    /**
     * @var ParametersAcceptor[]
     */
    private $variants;
    /**
     * @var ?Type
     */
    private $tentativeReturnType;
    /**
     * @param ParametersAcceptor[] $variants
     */
    public function __construct(string $name, \PHPStan\Reflection\ClassReflection $declaringClass, bool $isStatic, bool $isPrivate, bool $isPublic, bool $isAbstract, bool $isFinal, bool $isInternal, array $variants, ?Type $tentativeReturnType)
    {
        $this->name = $name;
        $this->declaringClass = $declaringClass;
        $this->isStatic = $isStatic;
        $this->isPrivate = $isPrivate;
        $this->isPublic = $isPublic;
        $this->isAbstract = $isAbstract;
        $this->isFinal = $isFinal;
        $this->isInternal = $isInternal;
        $this->variants = $variants;
        $this->tentativeReturnType = $tentativeReturnType;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getDeclaringClass() : \PHPStan\Reflection\ClassReflection
    {
        return $this->declaringClass;
    }
    public function isStatic() : bool
    {
        return $this->isStatic;
    }
    public function isPrivate() : bool
    {
        return $this->isPrivate;
    }
    public function isPublic() : bool
    {
        return $this->isPublic;
    }
    public function isAbstract() : bool
    {
        return $this->isAbstract;
    }
    public function isFinal() : bool
    {
        return $this->isFinal;
    }
    public function isInternal() : bool
    {
        return $this->isInternal;
    }
    public function getDocComment() : ?string
    {
        return null;
    }
    /**
     * @return ParametersAcceptor[]
     */
    public function getVariants() : array
    {
        return $this->variants;
    }
    public function getTentativeReturnType() : ?Type
    {
        return $this->tentativeReturnType;
    }
}
