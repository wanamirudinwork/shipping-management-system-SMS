<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
/** @api */
class NonAcceptingNeverType extends \PHPStan\Type\NeverType
{
    /** @api */
    public function __construct()
    {
        parent::__construct(\true);
    }
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : TrinaryLogic
    {
        return $this->isSuperTypeOfWithReason($type)->result;
    }
    public function isSuperTypeOfWithReason(\PHPStan\Type\Type $type) : \PHPStan\Type\IsSuperTypeOfResult
    {
        if ($type instanceof self) {
            return \PHPStan\Type\IsSuperTypeOfResult::createYes();
        }
        if ($type instanceof parent) {
            return \PHPStan\Type\IsSuperTypeOfResult::createMaybe();
        }
        return \PHPStan\Type\IsSuperTypeOfResult::createNo();
    }
    public function acceptsWithReason(\PHPStan\Type\Type $type, bool $strictTypes) : \PHPStan\Type\AcceptsResult
    {
        if ($type instanceof \PHPStan\Type\NeverType) {
            return \PHPStan\Type\AcceptsResult::createYes();
        }
        return \PHPStan\Type\AcceptsResult::createNo();
    }
    public function describe(\PHPStan\Type\VerbosityLevel $level) : string
    {
        return 'never';
    }
}
