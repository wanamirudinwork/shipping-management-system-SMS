<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\ReflectionProvider\ReflectionProviderProvider;
final class ConstantResolverFactory
{
    /**
     * @var ReflectionProviderProvider
     */
    private $reflectionProviderProvider;
    /**
     * @var Container
     */
    private $container;
    public function __construct(ReflectionProviderProvider $reflectionProviderProvider, Container $container)
    {
        $this->reflectionProviderProvider = $reflectionProviderProvider;
        $this->container = $container;
    }
    public function create() : \PHPStan\Analyser\ConstantResolver
    {
        return new \PHPStan\Analyser\ConstantResolver($this->reflectionProviderProvider, $this->container->getParameter('dynamicConstantNames'));
    }
}
