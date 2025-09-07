<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;
/**
 * @api
 * @final
 */
class MethodTagParameter
{
    /**
     * @var Type
     */
    private $type;
    /**
     * @var PassedByReference
     */
    private $passedByReference;
    /**
     * @var bool
     */
    private $isOptional;
    /**
     * @var bool
     */
    private $isVariadic;
    /**
     * @var ?Type
     */
    private $defaultValue;
    public function __construct(Type $type, PassedByReference $passedByReference, bool $isOptional, bool $isVariadic, ?Type $defaultValue)
    {
        $this->type = $type;
        $this->passedByReference = $passedByReference;
        $this->isOptional = $isOptional;
        $this->isVariadic = $isVariadic;
        $this->defaultValue = $defaultValue;
    }
    public function getType() : Type
    {
        return $this->type;
    }
    public function passedByReference() : PassedByReference
    {
        return $this->passedByReference;
    }
    public function isOptional() : bool
    {
        return $this->isOptional;
    }
    public function isVariadic() : bool
    {
        return $this->isVariadic;
    }
    public function getDefaultValue() : ?Type
    {
        return $this->defaultValue;
    }
}
