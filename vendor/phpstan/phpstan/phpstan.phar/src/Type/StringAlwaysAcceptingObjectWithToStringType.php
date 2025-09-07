<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Reflection\ReflectionProviderStaticAccessor;
use PHPStan\TrinaryLogic;
class StringAlwaysAcceptingObjectWithToStringType extends \PHPStan\Type\StringType
{
    public function isSuperTypeOf(\PHPStan\Type\Type $type) : TrinaryLogic
    {
        return $this->isSuperTypeOfWithReason($type)->result;
    }
    public function isSuperTypeOfWithReason(\PHPStan\Type\Type $type) : \PHPStan\Type\IsSuperTypeOfResult
    {
        if ($type instanceof \PHPStan\Type\CompoundType) {
            return $type->isSubTypeOfWithReason($this);
        }
        $thatClassNames = $type->getObjectClassNames();
        if ($thatClassNames === []) {
            return parent::isSuperTypeOfWithReason($type);
        }
        $result = \PHPStan\Type\IsSuperTypeOfResult::createNo();
        $reflectionProvider = ReflectionProviderStaticAccessor::getInstance();
        foreach ($thatClassNames as $thatClassName) {
            if (!$reflectionProvider->hasClass($thatClassName)) {
                return \PHPStan\Type\IsSuperTypeOfResult::createNo();
            }
            $typeClass = $reflectionProvider->getClass($thatClassName);
            $result = $result->or(\PHPStan\Type\IsSuperTypeOfResult::createFromBoolean($typeClass->hasNativeMethod('__toString')));
        }
        return $result;
    }
    public function acceptsWithReason(\PHPStan\Type\Type $type, bool $strictTypes) : \PHPStan\Type\AcceptsResult
    {
        $thatClassNames = $type->getObjectClassNames();
        if ($thatClassNames === []) {
            return parent::acceptsWithReason($type, $strictTypes);
        }
        $result = \PHPStan\Type\AcceptsResult::createNo();
        $reflectionProvider = ReflectionProviderStaticAccessor::getInstance();
        foreach ($thatClassNames as $thatClassName) {
            if (!$reflectionProvider->hasClass($thatClassName)) {
                return \PHPStan\Type\AcceptsResult::createNo();
            }
            $typeClass = $reflectionProvider->getClass($thatClassName);
            $result = $result->or(\PHPStan\Type\AcceptsResult::createFromBoolean($typeClass->hasNativeMethod('__toString')));
        }
        return $result;
    }
}
