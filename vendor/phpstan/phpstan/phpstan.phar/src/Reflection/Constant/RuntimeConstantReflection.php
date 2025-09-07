<?php

declare (strict_types=1);
namespace PHPStan\Reflection\Constant;

use PHPStan\Reflection\GlobalConstantReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;
final class RuntimeConstantReflection implements GlobalConstantReflection
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var Type
     */
    private $valueType;
    /**
     * @var ?string
     */
    private $fileName;
    /**
     * @var TrinaryLogic
     */
    private $isDeprecated;
    /**
     * @var ?string
     */
    private $deprecatedDescription;
    public function __construct(string $name, Type $valueType, ?string $fileName, TrinaryLogic $isDeprecated, ?string $deprecatedDescription)
    {
        $this->name = $name;
        $this->valueType = $valueType;
        $this->fileName = $fileName;
        $this->isDeprecated = $isDeprecated;
        $this->deprecatedDescription = $deprecatedDescription;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getValueType() : Type
    {
        return $this->valueType;
    }
    public function getFileName() : ?string
    {
        return $this->fileName;
    }
    public function isDeprecated() : TrinaryLogic
    {
        return $this->isDeprecated;
    }
    public function getDeprecatedDescription() : ?string
    {
        return $this->deprecatedDescription;
    }
    public function isInternal() : TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
}
