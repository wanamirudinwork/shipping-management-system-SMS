<?php

declare (strict_types=1);
namespace PHPStan\Type;

use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\AccessoryLiteralStringType;
use PHPStan\Type\Accessory\AccessoryLowercaseStringType;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\Accessory\AccessoryNonFalsyStringType;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Accessory\AccessoryUppercaseStringType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\GenericStaticType;
use PHPStan\Type\Generic\TemplateType;
final class VerbosityLevel
{
    /**
     * @var self::*
     */
    private $value;
    private const TYPE_ONLY = 1;
    private const VALUE = 2;
    private const PRECISE = 3;
    private const CACHE = 4;
    /** @var self[] */
    private static $registry;
    /**
     * @param self::* $value
     */
    private function __construct(int $value)
    {
        $this->value = $value;
    }
    /**
     * @param self::* $value
     */
    private static function create(int $value) : self
    {
        self::$registry[$value] = self::$registry[$value] ?? new self($value);
        return self::$registry[$value];
    }
    /** @return self::* */
    public function getLevelValue() : int
    {
        return $this->value;
    }
    /** @api */
    public static function typeOnly() : self
    {
        return self::create(self::TYPE_ONLY);
    }
    /** @api */
    public static function value() : self
    {
        return self::create(self::VALUE);
    }
    /** @api */
    public static function precise() : self
    {
        return self::create(self::PRECISE);
    }
    /** @api */
    public static function cache() : self
    {
        return self::create(self::CACHE);
    }
    public function isTypeOnly() : bool
    {
        return $this->value === self::TYPE_ONLY;
    }
    public function isValue() : bool
    {
        return $this->value === self::VALUE;
    }
    public function isPrecise() : bool
    {
        return $this->value === self::PRECISE;
    }
    /** @api */
    public static function getRecommendedLevelByType(\PHPStan\Type\Type $acceptingType, ?\PHPStan\Type\Type $acceptedType = null) : self
    {
        $moreVerboseCallback = static function (\PHPStan\Type\Type $type, callable $traverse) use(&$moreVerbose, &$veryVerbose) : \PHPStan\Type\Type {
            if ($type->isCallable()->yes()) {
                $moreVerbose = \true;
                // Keep checking if we need to be very verbose.
                return $traverse($type);
            }
            if ($type->isConstantValue()->yes() && $type->isNull()->no()) {
                $moreVerbose = \true;
                // For ConstantArrayType we need to keep checking if we need to be very verbose.
                if (!$type->isArray()->no()) {
                    return $traverse($type);
                }
                return $type;
            }
            if ($type instanceof AccessoryNonEmptyStringType || $type instanceof AccessoryNonFalsyStringType || $type instanceof AccessoryLiteralStringType || $type instanceof AccessoryNumericStringType || $type instanceof NonEmptyArrayType || $type instanceof AccessoryArrayListType) {
                $moreVerbose = \true;
                return $type;
            }
            if ($type instanceof AccessoryLowercaseStringType || $type instanceof AccessoryUppercaseStringType) {
                $moreVerbose = \true;
                $veryVerbose = \true;
                return $type;
            }
            if ($type instanceof \PHPStan\Type\IntegerRangeType) {
                $moreVerbose = \true;
                return $type;
            }
            return $traverse($type);
        };
        /** @var bool $moreVerbose */
        $moreVerbose = \false;
        /** @var bool $veryVerbose */
        $veryVerbose = \false;
        \PHPStan\Type\TypeTraverser::map($acceptingType, $moreVerboseCallback);
        if ($veryVerbose) {
            return self::precise();
        }
        if ($moreVerbose) {
            $verbosity = self::value();
        }
        if ($acceptedType === null) {
            return $verbosity ?? self::typeOnly();
        }
        $containsInvariantTemplateType = \false;
        \PHPStan\Type\TypeTraverser::map($acceptingType, static function (\PHPStan\Type\Type $type, callable $traverse) use(&$containsInvariantTemplateType) : \PHPStan\Type\Type {
            if ($type instanceof GenericObjectType || $type instanceof GenericStaticType) {
                $reflection = $type->getClassReflection();
                if ($reflection !== null) {
                    $templateTypeMap = $reflection->getTemplateTypeMap();
                    foreach ($templateTypeMap->getTypes() as $templateType) {
                        if (!$templateType instanceof TemplateType) {
                            continue;
                        }
                        if (!$templateType->getVariance()->invariant()) {
                            continue;
                        }
                        $containsInvariantTemplateType = \true;
                        return $type;
                    }
                }
            }
            return $traverse($type);
        });
        if (!$containsInvariantTemplateType) {
            return $verbosity ?? self::typeOnly();
        }
        /** @var bool $moreVerbose */
        $moreVerbose = \false;
        /** @var bool $veryVerbose */
        $veryVerbose = \false;
        \PHPStan\Type\TypeTraverser::map($acceptedType, $moreVerboseCallback);
        if ($veryVerbose) {
            return self::precise();
        }
        return $moreVerbose ? self::value() : $verbosity ?? self::typeOnly();
    }
    /**
     * @param callable(): string $typeOnlyCallback
     * @param callable(): string $valueCallback
     * @param callable(): string|null $preciseCallback
     * @param callable(): string|null $cacheCallback
     */
    public function handle(callable $typeOnlyCallback, callable $valueCallback, ?callable $preciseCallback = null, ?callable $cacheCallback = null) : string
    {
        if ($this->value === self::TYPE_ONLY) {
            return $typeOnlyCallback();
        }
        if ($this->value === self::VALUE) {
            return $valueCallback();
        }
        if ($this->value === self::PRECISE) {
            if ($preciseCallback !== null) {
                return $preciseCallback();
            }
            return $valueCallback();
        }
        if ($cacheCallback !== null) {
            return $cacheCallback();
        }
        if ($preciseCallback !== null) {
            return $preciseCallback();
        }
        return $valueCallback();
    }
}
