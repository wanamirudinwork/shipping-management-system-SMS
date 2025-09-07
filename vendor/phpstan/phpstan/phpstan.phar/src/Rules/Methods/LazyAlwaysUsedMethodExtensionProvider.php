<?php

declare (strict_types=1);
namespace PHPStan\Rules\Methods;

use PHPStan\DependencyInjection\Container;
final class LazyAlwaysUsedMethodExtensionProvider implements \PHPStan\Rules\Methods\AlwaysUsedMethodExtensionProvider
{
    /**
     * @var Container
     */
    private $container;
    /** @var AlwaysUsedMethodExtension[]|null */
    private $extensions = null;
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    public function getExtensions() : array
    {
        return $this->extensions = $this->extensions ?? $this->container->getServicesByTag(static::EXTENSION_TAG);
    }
}
