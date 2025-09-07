<?php

declare (strict_types=1);
namespace PHPStan\Internal;

use function clearstatcache;
use function is_dir;
use function mkdir;
final class DirectoryCreator
{
    /**
     * @throws DirectoryCreatorException if unable to create directory.
     */
    public static function ensureDirectoryExists(string $directory, int $mode = 0755, bool $recursive = \true) : void
    {
        if (is_dir($directory)) {
            return;
        }
        if (@mkdir($directory, $mode, $recursive)) {
            return;
        }
        clearstatcache();
        if (!is_dir($directory)) {
            throw new \PHPStan\Internal\DirectoryCreatorException($directory);
        }
    }
}
