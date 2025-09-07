<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Exception;

use RuntimeException;
use function gettype;
use function is_object;
use function sprintf;
class InvalidFileInfo extends RuntimeException
{
    /**
     * @param mixed $nonSplFileInfo
     */
    public static function fromNonSplFileInfo($nonSplFileInfo) : self
    {
        return new self(sprintf('Expected an iterator of SplFileInfo instances, %s given instead', is_object($nonSplFileInfo) ? \get_class($nonSplFileInfo) : gettype($nonSplFileInfo)));
    }
}
