<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Php\PhpVersion;
use PHPStan\ShouldNotHappenException;
final class PhpVersionStaticAccessor
{
    /**
     * @var ?PhpVersion
     */
    private static $instance = null;
    private function __construct()
    {
    }
    public static function registerInstance(PhpVersion $phpVersion) : void
    {
        self::$instance = $phpVersion;
    }
    public static function getInstance() : PhpVersion
    {
        if (self::$instance === null) {
            throw new ShouldNotHappenException();
        }
        return self::$instance;
    }
}
