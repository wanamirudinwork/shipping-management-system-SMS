<?php

declare (strict_types=1);
namespace PHPStan\Type\Regex;

final class RegexNonCapturingGroup
{
    /**
     * @readonly
     * @var ?RegexAlternation
     */
    private $alternation;
    /**
     * @readonly
     * @var bool
     */
    private $inOptionalQuantification;
    /**
     * @readonly
     * @var RegexCapturingGroup|RegexNonCapturingGroup|null
     */
    private $parent;
    /**
     * @readonly
     * @var bool
     */
    private $resetGroupCounter;
    /**
     * @param RegexCapturingGroup|RegexNonCapturingGroup|null $parent
     */
    public function __construct(?\PHPStan\Type\Regex\RegexAlternation $alternation, bool $inOptionalQuantification, $parent, bool $resetGroupCounter)
    {
        $this->alternation = $alternation;
        $this->inOptionalQuantification = $inOptionalQuantification;
        $this->parent = $parent;
        $this->resetGroupCounter = $resetGroupCounter;
    }
    /** @phpstan-assert-if-true !null $this->getAlternationId() */
    public function inAlternation() : bool
    {
        return $this->alternation !== null;
    }
    public function getAlternationId() : ?int
    {
        if ($this->alternation === null) {
            return null;
        }
        return $this->alternation->getId();
    }
    public function isOptional() : bool
    {
        return $this->inAlternation() || $this->inOptionalQuantification || $this->parent !== null && $this->parent->isOptional();
    }
    public function isTopLevel() : bool
    {
        return $this->parent === null || $this->parent instanceof \PHPStan\Type\Regex\RegexNonCapturingGroup && $this->parent->isTopLevel();
    }
    /**
     * @return RegexCapturingGroup|RegexNonCapturingGroup|null
     */
    public function getParent()
    {
        return $this->parent;
    }
    public function resetsGroupCounter() : bool
    {
        return $this->resetGroupCounter;
    }
}
