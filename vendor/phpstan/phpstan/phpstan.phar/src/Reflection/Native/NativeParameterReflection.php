<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Native;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
final class NativeParameterReflection implements ParameterReflection
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var bool
     */
    private $optional;
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
    private $variadic;
    /**
     * @var ?Type
     */
    private $defaultValue;
    public function __construct(string $name, bool $optional, Type $type, PassedByReference $passedByReference, bool $variadic, ?Type $defaultValue)
    {
        $this->name = $name;
        $this->optional = $optional;
        $this->type = $type;
        $this->passedByReference = $passedByReference;
        $this->variadic = $variadic;
        $this->defaultValue = $defaultValue;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function isOptional() : bool
    {
        return $this->optional;
    }
    public function getType() : Type
    {
        return $this->type;
    }
    public function passedByReference() : PassedByReference
    {
        return $this->passedByReference;
    }
    public function isVariadic() : bool
    {
        return $this->variadic;
    }
    public function getDefaultValue() : ?Type
    {
        return $this->defaultValue;
    }
    public function union(self $other) : self
    {
        return new self($this->name, $this->optional && $other->optional, TypeCombinator::union($this->type, $other->type), $this->passedByReference->combine($other->passedByReference), $this->variadic && $other->variadic, $this->optional && $other->optional ? $this->defaultValue : null);
    }
    /**
     * @param mixed[] $properties
     */
    public static function __set_state(array $properties) : self
    {
        return new self($properties['name'], $properties['optional'], $properties['type'], $properties['passedByReference'], $properties['variadic'], $properties['defaultValue']);
    }
}
