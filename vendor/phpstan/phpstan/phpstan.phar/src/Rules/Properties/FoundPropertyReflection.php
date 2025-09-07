<?php

declare (strict_types=1);
namespace PHPStan\Rules\Properties;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\WrapperPropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
final class FoundPropertyReflection implements ExtendedPropertyReflection
{
    /**
     * @var ExtendedPropertyReflection
     */
    private $originalPropertyReflection;
    /**
     * @var Scope
     */
    private $scope;
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var Type
     */
    private $readableType;
    /**
     * @var Type
     */
    private $writableType;
    public function __construct(ExtendedPropertyReflection $originalPropertyReflection, Scope $scope, string $propertyName, Type $readableType, Type $writableType)
    {
        $this->originalPropertyReflection = $originalPropertyReflection;
        $this->scope = $scope;
        $this->propertyName = $propertyName;
        $this->readableType = $readableType;
        $this->writableType = $writableType;
    }
    public function getScope() : Scope
    {
        return $this->scope;
    }
    public function getDeclaringClass() : ClassReflection
    {
        return $this->originalPropertyReflection->getDeclaringClass();
    }
    public function getName() : string
    {
        return $this->propertyName;
    }
    public function isStatic() : bool
    {
        return $this->originalPropertyReflection->isStatic();
    }
    public function isPrivate() : bool
    {
        return $this->originalPropertyReflection->isPrivate();
    }
    public function isPublic() : bool
    {
        return $this->originalPropertyReflection->isPublic();
    }
    public function getDocComment() : ?string
    {
        return $this->originalPropertyReflection->getDocComment();
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
        return $this->originalPropertyReflection->canChangeTypeAfterAssignment();
    }
    public function isReadable() : bool
    {
        return $this->originalPropertyReflection->isReadable();
    }
    public function isWritable() : bool
    {
        return $this->originalPropertyReflection->isWritable();
    }
    public function isDeprecated() : TrinaryLogic
    {
        return $this->originalPropertyReflection->isDeprecated();
    }
    public function getDeprecatedDescription() : ?string
    {
        return $this->originalPropertyReflection->getDeprecatedDescription();
    }
    public function isInternal() : TrinaryLogic
    {
        return $this->originalPropertyReflection->isInternal();
    }
    public function isNative() : bool
    {
        return $this->getNativeReflection() !== null;
    }
    public function getNativeType() : ?Type
    {
        $reflection = $this->getNativeReflection();
        if ($reflection === null) {
            return null;
        }
        return $reflection->getNativeType();
    }
    public function getNativeReflection() : ?PhpPropertyReflection
    {
        $reflection = $this->originalPropertyReflection;
        while ($reflection instanceof WrapperPropertyReflection) {
            $reflection = $reflection->getOriginalReflection();
        }
        if (!$reflection instanceof PhpPropertyReflection) {
            return null;
        }
        return $reflection;
    }
}
