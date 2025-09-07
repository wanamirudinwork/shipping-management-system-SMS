<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\ShouldNotHappenException;
final class ReflectionProviderStaticAccessor
{
    /**
     * @var ?ReflectionProvider
     */
    private static $instance = null;
    private function __construct()
    {
    }
    public static function registerInstance(\PHPStan\Reflection\ReflectionProvider $reflectionProvider) : void
    {
        self::$instance = $reflectionProvider;
    }
    public static function getInstance() : \PHPStan\Reflection\ReflectionProvider
    {
        if (self::$instance === null) {
            throw new ShouldNotHappenException();
        }
        return self::$instance;
    }
}
