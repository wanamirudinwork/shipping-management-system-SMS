<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
/** @api */
interface CompoundType extends \PHPStan\Type\Type
{
    public function isSubTypeOf(\PHPStan\Type\Type $otherType) : TrinaryLogic;
    /**
     * This is like isSubTypeOf() but gives reasons
     * why the type was not/might not be accepted in some non-intuitive scenarios.
     *
     * In PHPStan 2.0 this method will be removed and the return type of isSubTypeOf()
     * will change to IsSuperTypeOfResult.
     */
    public function isSubTypeOfWithReason(\PHPStan\Type\Type $otherType) : \PHPStan\Type\IsSuperTypeOfResult;
    public function isAcceptedBy(\PHPStan\Type\Type $acceptingType, bool $strictTypes) : TrinaryLogic;
    public function isAcceptedWithReasonBy(\PHPStan\Type\Type $acceptingType, bool $strictTypes) : \PHPStan\Type\AcceptsResult;
    public function isGreaterThan(\PHPStan\Type\Type $otherType) : TrinaryLogic;
    public function isGreaterThanOrEqual(\PHPStan\Type\Type $otherType) : TrinaryLogic;
}
