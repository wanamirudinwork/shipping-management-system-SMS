<?php

declare (strict_types=1);
namespace PHPStan\Type;

final class DirectTypeAliasResolverProvider implements \PHPStan\Type\TypeAliasResolverProvider
{
    /**
     * @var TypeAliasResolver
     */
    private $typeAliasResolver;
    public function __construct(\PHPStan\Type\TypeAliasResolver $typeAliasResolver)
    {
        $this->typeAliasResolver = $typeAliasResolver;
    }
    public function getTypeAliasResolver() : \PHPStan\Type\TypeAliasResolver
    {
        return $this->typeAliasResolver;
    }
}
