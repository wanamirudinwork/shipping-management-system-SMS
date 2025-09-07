<?php

declare (strict_types=1);
namespace PHPStan\Type\Regex;

use PHPStan\TrinaryLogic;
/** @immutable */
final class RegexGroupWalkResult
{
    /**
     * @var bool
     */
    private $inOptionalQuantification;
    /**
     * @var array<string>|null
     */
    private $onlyLiterals;
    /**
     * @var TrinaryLogic
     */
    private $isNonEmpty;
    /**
     * @var TrinaryLogic
     */
    private $isNonFalsy;
    /**
     * @var TrinaryLogic
     */
    private $isNumeric;
    /**
     * @param array<string>|null $onlyLiterals
     */
    public function __construct(bool $inOptionalQuantification, ?array $onlyLiterals, TrinaryLogic $isNonEmpty, TrinaryLogic $isNonFalsy, TrinaryLogic $isNumeric)
    {
        $this->inOptionalQuantification = $inOptionalQuantification;
        $this->onlyLiterals = $onlyLiterals;
        $this->isNonEmpty = $isNonEmpty;
        $this->isNonFalsy = $isNonFalsy;
        $this->isNumeric = $isNumeric;
    }
    public static function createEmpty() : self
    {
        return new self(\false, [], TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe(), TrinaryLogic::createMaybe());
    }
    public function inOptionalQuantification(bool $inOptionalQuantification) : self
    {
        return new self($inOptionalQuantification, $this->onlyLiterals, $this->isNonEmpty, $this->isNonFalsy, $this->isNumeric);
    }
    /**
     * @param array<string>|null $onlyLiterals
     */
    public function onlyLiterals(?array $onlyLiterals) : self
    {
        return new self($this->inOptionalQuantification, $onlyLiterals, $this->isNonEmpty, $this->isNonFalsy, $this->isNumeric);
    }
    public function nonEmpty(TrinaryLogic $nonEmpty) : self
    {
        return new self($this->inOptionalQuantification, $this->onlyLiterals, $nonEmpty, $this->isNonFalsy, $this->isNumeric);
    }
    public function nonFalsy(TrinaryLogic $nonFalsy) : self
    {
        return new self($this->inOptionalQuantification, $this->onlyLiterals, $this->isNonEmpty, $nonFalsy, $this->isNumeric);
    }
    public function numeric(TrinaryLogic $numeric) : self
    {
        return new self($this->inOptionalQuantification, $this->onlyLiterals, $this->isNonEmpty, $this->isNonFalsy, $numeric);
    }
    public function isInOptionalQuantification() : bool
    {
        return $this->inOptionalQuantification;
    }
    /**
     * @return array<string>|null
     */
    public function getOnlyLiterals() : ?array
    {
        return $this->onlyLiterals;
    }
    public function isNonEmpty() : TrinaryLogic
    {
        return $this->isNonEmpty;
    }
    public function isNonFalsy() : TrinaryLogic
    {
        return $this->isNonFalsy;
    }
    public function isNumeric() : TrinaryLogic
    {
        return $this->isNumeric;
    }
}
