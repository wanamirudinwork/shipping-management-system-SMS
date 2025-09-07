<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Type\Type;
/**
 * @api
 * @final
 */
class EnumCaseReflection
{
    /**
     * @var ClassReflection
     */
    private $declaringEnum;
    /**
     * @var string
     */
    private $name;
    /**
     * @var ?Type
     */
    private $backingValueType;
    public function __construct(\PHPStan\Reflection\ClassReflection $declaringEnum, string $name, ?Type $backingValueType)
    {
        $this->declaringEnum = $declaringEnum;
        $this->name = $name;
        $this->backingValueType = $backingValueType;
    }
    public function getDeclaringEnum() : \PHPStan\Reflection\ClassReflection
    {
        return $this->declaringEnum;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getBackingValueType() : ?Type
    {
        return $this->backingValueType;
    }
}
