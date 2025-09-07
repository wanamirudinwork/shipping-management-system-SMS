<?php

declare (strict_types=1);
namespace PHPStan\Rules\Constants;

use PHPStan\DependencyInjection\Container;
final class LazyAlwaysUsedClassConstantsExtensionProvider implements \PHPStan\Rules\Constants\AlwaysUsedClassConstantsExtensionProvider
{
    /**
     * @var Container
     */
    private $container;
    /** @var AlwaysUsedClassConstantsExtension[]|null */
    private $extensions = null;
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    public function getExtensions() : array
    {
        if ($this->extensions === null) {
            $this->extensions = $this->container->getServicesByTag(\PHPStan\Rules\Constants\AlwaysUsedClassConstantsExtensionProvider::EXTENSION_TAG);
        }
        return $this->extensions;
    }
}
