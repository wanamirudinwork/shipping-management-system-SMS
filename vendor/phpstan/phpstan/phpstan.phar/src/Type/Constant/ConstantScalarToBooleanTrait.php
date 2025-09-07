<?php

declare (strict_types=1);
namespace PHPStan\Type\Constant;

use PHPStan\Type\BooleanType;
trait ConstantScalarToBooleanTrait
{
    public function toBoolean() : BooleanType
    {
        return new \PHPStan\Type\Constant\ConstantBooleanType((bool) $this->value);
    }
}
