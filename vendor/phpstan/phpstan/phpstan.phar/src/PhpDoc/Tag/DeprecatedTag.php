<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

/**
 * @api
 * @final
 */
class DeprecatedTag
{
    /**
     * @var ?string
     */
    private $message;
    public function __construct(?string $message)
    {
        $this->message = $message;
    }
    public function getMessage() : ?string
    {
        return $this->message;
    }
}
