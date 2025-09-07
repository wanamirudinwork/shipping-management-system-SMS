<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Annotations;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
final class AnnotationPropertyReflection implements ExtendedPropertyReflection
{
    /**
     * @var ClassReflection
     */
    private $declaringClass;
    /**
     * @var Type
     */
    private $readableType;
    /**
     * @var Type
     */
    private $writableType;
    /**
     * @var bool
     */
    private $readable;
    /**
     * @var bool
     */
    private $writable;
    public function __construct(ClassReflection $declaringClass, Type $readableType, Type $writableType, bool $readable, bool $writable)
    {
        $this->declaringClass = $declaringClass;
        $this->readableType = $readableType;
        $this->writableType = $writableType;
        $this->readable = $readable;
        $this->writable = $writable;
    }
    public function getDeclaringClass() : ClassReflection
    {
        return $this->declaringClass;
    }
    public function isStatic() : bool
    {
        return \false;
    }
    public function isPrivate() : bool
    {
        return \false;
    }
    public function isPublic() : bool
    {
        return \true;
    }
    public function getReadableType() : Type
    {
        return $this->readableType;
    }
    public function getWritableType() : Type
    {
        return $this->writableType;
    }
    public function canChangeTypeAfterAssignment() : bool
    {
        return $this->readableType->equals($this->writableType);
    }
    public function isReadable() : bool
    {
        return $this->readable;
    }
    public function isWritable() : bool
    {
        return $this->writable;
    }
    public function isDeprecated() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function getDeprecatedDescription() : ?string
    {
        return null;
    }
    public function isInternal() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
    public function getDocComment() : ?string
    {
        return null;
    }
}
