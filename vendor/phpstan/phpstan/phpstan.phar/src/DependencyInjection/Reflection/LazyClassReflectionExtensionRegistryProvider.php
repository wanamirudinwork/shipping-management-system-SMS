<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection\Reflection;

use PHPStan\Broker\Broker;
use PHPStan\Broker\BrokerFactory;
use PHPStan\DependencyInjection\Container;
use PHPStan\Reflection\Annotations\AnnotationsMethodsClassReflectionExtension;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\ClassReflectionExtensionRegistry;
use PHPStan\Reflection\Mixin\MixinMethodsClassReflectionExtension;
use PHPStan\Reflection\Mixin\MixinPropertiesClassReflectionExtension;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\RequireExtension\RequireExtendsMethodsClassReflectionExtension;
use PHPStan\Reflection\RequireExtension\RequireExtendsPropertiesClassReflectionExtension;
use function array_merge;
final class LazyClassReflectionExtensionRegistryProvider implements \PHPStan\DependencyInjection\Reflection\ClassReflectionExtensionRegistryProvider
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var ?ClassReflectionExtensionRegistry
     */
    private $registry = null;
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    public function getRegistry() : ClassReflectionExtensionRegistry
    {
        if ($this->registry === null) {
            $phpClassReflectionExtension = $this->container->getByType(PhpClassReflectionExtension::class);
            $annotationsMethodsClassReflectionExtension = $this->container->getByType(AnnotationsMethodsClassReflectionExtension::class);
            $annotationsPropertiesClassReflectionExtension = $this->container->getByType(AnnotationsPropertiesClassReflectionExtension::class);
            $mixinMethodsClassReflectionExtension = $this->container->getByType(MixinMethodsClassReflectionExtension::class);
            $mixinPropertiesClassReflectionExtension = $this->container->getByType(MixinPropertiesClassReflectionExtension::class);
            $this->registry = new ClassReflectionExtensionRegistry($this->container->getByType(Broker::class), array_merge([$phpClassReflectionExtension], $this->container->getServicesByTag(BrokerFactory::PROPERTIES_CLASS_REFLECTION_EXTENSION_TAG), [$annotationsPropertiesClassReflectionExtension, $mixinPropertiesClassReflectionExtension]), array_merge([$phpClassReflectionExtension], $this->container->getServicesByTag(BrokerFactory::METHODS_CLASS_REFLECTION_EXTENSION_TAG), [$annotationsMethodsClassReflectionExtension, $mixinMethodsClassReflectionExtension]), $this->container->getServicesByTag(BrokerFactory::ALLOWED_SUB_TYPES_CLASS_REFLECTION_EXTENSION_TAG), $this->container->getByType(RequireExtendsPropertiesClassReflectionExtension::class), $this->container->getByType(RequireExtendsMethodsClassReflectionExtension::class));
        }
        return $this->registry;
    }
}
