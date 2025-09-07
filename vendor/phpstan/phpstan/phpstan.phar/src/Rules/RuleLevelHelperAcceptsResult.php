<?php

declare (strict_types=1);
namespace PHPStan\Rules;

use function array_merge;
final class RuleLevelHelperAcceptsResult
{
    /**
     * @readonly
     * @var bool
     */
    public $result;
    /**
     * @readonly
     * @var list<string>
     */
    public $reasons;
    /**
     * @param list<string> $reasons
     */
    public function __construct(bool $result, array $reasons)
    {
        $this->result = $result;
        $this->reasons = $reasons;
    }
    public function and(self $other) : self
    {
        return new self($this->result && $other->result, array_merge($this->reasons, $other->reasons));
    }
    /**
     * @param callable(string): string $cb
     */
    public function decorateReasons(callable $cb) : self
    {
        $reasons = [];
        foreach ($this->reasons as $reason) {
            $reasons[] = $cb($reason);
        }
        return new self($this->result, $reasons);
    }
}
