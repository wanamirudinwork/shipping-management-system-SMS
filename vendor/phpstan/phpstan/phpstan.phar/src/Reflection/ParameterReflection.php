<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Type\Type;
/** @api */
interface ParameterReflection
{
    public function getName() : string;
    public function isOptional() : bool;
    public function getType() : Type;
    public function passedByReference() : \PHPStan\Reflection\PassedByReference;
    public function isVariadic() : bool;
    public function getDefaultValue() : ?Type;
}
