<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection\Neon;

final class OptionalPath
{
    /**
     * @readonly
     * @var string
     */
    public $path;
    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
