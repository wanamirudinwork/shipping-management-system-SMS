<?php

declare (strict_types=1);
namespace PHPStan\Type\Regex;

use PHPStan\Type\Type;
final class RegexCapturingGroup
{
    /**
     * @readonly
     * @var int
     */
    private $id;
    /**
     * @readonly
     * @var ?string
     */
    private $name;
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
     * @var Type
     */
    private $type;
    /**
     * @var bool
     */
    private $forceNonOptional = \false;
    /**
     * @var ?Type
     */
    private $forceType = null;
    /**
     * @param RegexCapturingGroup|RegexNonCapturingGroup|null $parent
     */
    public function __construct(int $id, ?string $name, ?\PHPStan\Type\Regex\RegexAlternation $alternation, bool $inOptionalQuantification, $parent, Type $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->alternation = $alternation;
        $this->inOptionalQuantification = $inOptionalQuantification;
        $this->parent = $parent;
        $this->type = $type;
    }
    public function getId() : int
    {
        return $this->id;
    }
    public function forceNonOptional() : void
    {
        $this->forceNonOptional = \true;
    }
    public function forceType(Type $type) : void
    {
        $this->forceType = $type;
    }
    public function clearOverrides() : void
    {
        $this->forceNonOptional = \false;
        $this->forceType = null;
    }
    public function resetsGroupCounter() : bool
    {
        return $this->parent instanceof \PHPStan\Type\Regex\RegexNonCapturingGroup && $this->parent->resetsGroupCounter();
    }
    /**
     * @phpstan-assert-if-true !null $this->getAlternationId()
     * @phpstan-assert-if-true !null $this->getAlternation()
     */
    public function inAlternation() : bool
    {
        return $this->alternation !== null;
    }
    public function getAlternation() : ?\PHPStan\Type\Regex\RegexAlternation
    {
        return $this->alternation;
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
        if ($this->forceNonOptional) {
            return \false;
        }
        return $this->inAlternation() || $this->inOptionalQuantification || $this->parent !== null && $this->parent->isOptional();
    }
    public function inOptionalQuantification() : bool
    {
        return $this->inOptionalQuantification;
    }
    public function inOptionalAlternation() : bool
    {
        if (!$this->inAlternation()) {
            return \false;
        }
        $parent = $this->parent;
        while ($parent !== null && $parent->getAlternationId() === $this->getAlternationId()) {
            if (!$parent instanceof \PHPStan\Type\Regex\RegexNonCapturingGroup) {
                return \false;
            }
            $parent = $parent->getParent();
        }
        return $parent !== null && $parent->isOptional();
    }
    public function isTopLevel() : bool
    {
        return $this->parent === null || $this->parent instanceof \PHPStan\Type\Regex\RegexNonCapturingGroup && $this->parent->isTopLevel();
    }
    /** @phpstan-assert-if-true !null $this->getName() */
    public function isNamed() : bool
    {
        return $this->name !== null;
    }
    public function getName() : ?string
    {
        return $this->name;
    }
    public function getType() : Type
    {
        if ($this->forceType !== null) {
            return $this->forceType;
        }
        return $this->type;
    }
}
