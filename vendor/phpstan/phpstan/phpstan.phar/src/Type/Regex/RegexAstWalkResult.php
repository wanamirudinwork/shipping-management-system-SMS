<?php

declare (strict_types=1);
namespace PHPStan\Type\Regex;

/** @immutable */
final class RegexAstWalkResult
{
    /**
     * @var int
     */
    private $alternationId;
    /**
     * @var int
     */
    private $captureGroupId;
    /**
     * @var array<int, RegexCapturingGroup>
     */
    private $capturingGroups;
    /**
     * @var list<string>
     */
    private $markVerbs;
    /**
     * @param array<int, RegexCapturingGroup> $capturingGroups
     * @param list<string> $markVerbs
     */
    public function __construct(int $alternationId, int $captureGroupId, array $capturingGroups, array $markVerbs)
    {
        $this->alternationId = $alternationId;
        $this->captureGroupId = $captureGroupId;
        $this->capturingGroups = $capturingGroups;
        $this->markVerbs = $markVerbs;
    }
    public static function createEmpty() : self
    {
        return new self(
            -1,
            // use different start-index for groups to make it easier to distinguish groupids from other ids
            100,
            [],
            []
        );
    }
    public function nextAlternationId() : self
    {
        return new self($this->alternationId + 1, $this->captureGroupId, $this->capturingGroups, $this->markVerbs);
    }
    public function nextCaptureGroupId() : self
    {
        return new self($this->alternationId, $this->captureGroupId + 1, $this->capturingGroups, $this->markVerbs);
    }
    public function addCapturingGroup(\PHPStan\Type\Regex\RegexCapturingGroup $group) : self
    {
        $capturingGroups = $this->capturingGroups;
        $capturingGroups[$group->getId()] = $group;
        return new self($this->alternationId, $this->captureGroupId, $capturingGroups, $this->markVerbs);
    }
    public function markVerb(string $markVerb) : self
    {
        $verbs = $this->markVerbs;
        $verbs[] = $markVerb;
        return new self($this->alternationId, $this->captureGroupId, $this->capturingGroups, $verbs);
    }
    public function getAlternationId() : int
    {
        return $this->alternationId;
    }
    public function getCaptureGroupId() : int
    {
        return $this->captureGroupId;
    }
    /**
     * @return array<int, RegexCapturingGroup>
     */
    public function getCapturingGroups() : array
    {
        return $this->capturingGroups;
    }
    /**
     * @return list<string>
     */
    public function getMarkVerbs() : array
    {
        return $this->markVerbs;
    }
}
