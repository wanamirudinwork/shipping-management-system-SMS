<?php

declare (strict_types=1);
namespace PHPStan\Reflection\ReflectionProvider;

use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider;
final class LazyReflectionProviderProvider implements \PHPStan\Reflection\ReflectionProvider\ReflectionProviderProvider
{
    /**
     * @var Container
     */
    private $container;
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    public function getReflectionProvider() : ReflectionProvider
    {
        return $this->container->getByType(ReflectionProvider::class);
    }
}
