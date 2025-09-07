<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\RequireExtension\RequireExtendsMethodsClassReflectionExtension;
use PHPStan\Reflection\RequireExtension\RequireExtendsPropertiesClassReflectionExtension;
use function array_merge;
final class ClassReflectionExtensionRegistry
{
    /**
     * @var PropertiesClassReflectionExtension[]
     */
    private $propertiesClassReflectionExtensions;
    /**
     * @var MethodsClassReflectionExtension[]
     */
    private $methodsClassReflectionExtensions;
    /**
     * @var AllowedSubTypesClassReflectionExtension[]
     */
    private $allowedSubTypesClassReflectionExtensions;
    /**
     * @var RequireExtendsPropertiesClassReflectionExtension
     */
    private $requireExtendsPropertiesClassReflectionExtension;
    /**
     * @var RequireExtendsMethodsClassReflectionExtension
     */
    private $requireExtendsMethodsClassReflectionExtension;
    /**
     * @param PropertiesClassReflectionExtension[] $propertiesClassReflectionExtensions
     * @param MethodsClassReflectionExtension[] $methodsClassReflectionExtensions
     * @param AllowedSubTypesClassReflectionExtension[] $allowedSubTypesClassReflectionExtensions
     */
    public function __construct(Broker $broker, array $propertiesClassReflectionExtensions, array $methodsClassReflectionExtensions, array $allowedSubTypesClassReflectionExtensions, RequireExtendsPropertiesClassReflectionExtension $requireExtendsPropertiesClassReflectionExtension, RequireExtendsMethodsClassReflectionExtension $requireExtendsMethodsClassReflectionExtension)
    {
        $this->propertiesClassReflectionExtensions = $propertiesClassReflectionExtensions;
        $this->methodsClassReflectionExtensions = $methodsClassReflectionExtensions;
        $this->allowedSubTypesClassReflectionExtensions = $allowedSubTypesClassReflectionExtensions;
        $this->requireExtendsPropertiesClassReflectionExtension = $requireExtendsPropertiesClassReflectionExtension;
        $this->requireExtendsMethodsClassReflectionExtension = $requireExtendsMethodsClassReflectionExtension;
        foreach (array_merge($propertiesClassReflectionExtensions, $methodsClassReflectionExtensions, $allowedSubTypesClassReflectionExtensions) as $extension) {
            if (!$extension instanceof \PHPStan\Reflection\BrokerAwareExtension) {
                continue;
            }
            $extension->setBroker($broker);
        }
    }
    /**
     * @return PropertiesClassReflectionExtension[]
     */
    public function getPropertiesClassReflectionExtensions() : array
    {
        return $this->propertiesClassReflectionExtensions;
    }
    /**
     * @return MethodsClassReflectionExtension[]
     */
    public function getMethodsClassReflectionExtensions() : array
    {
        return $this->methodsClassReflectionExtensions;
    }
    /**
     * @return AllowedSubTypesClassReflectionExtension[]
     */
    public function getAllowedSubTypesClassReflectionExtensions() : array
    {
        return $this->allowedSubTypesClassReflectionExtensions;
    }
    public function getRequireExtendsPropertyClassReflectionExtension() : RequireExtendsPropertiesClassReflectionExtension
    {
        return $this->requireExtendsPropertiesClassReflectionExtension;
    }
    public function getRequireExtendsMethodsClassReflectionExtension() : RequireExtendsMethodsClassReflectionExtension
    {
        return $this->requireExtendsMethodsClassReflectionExtension;
    }
}
