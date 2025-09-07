<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use function array_key_exists;
use function spl_object_id;
use function sprintf;
final class MemoizingSourceLocator implements \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
{
    /** @var array<string, Reflection|null> indexed by reflector key and identifier cache key */
    private $cacheByIdentifierKeyAndOid = [];
    /** @var array<string, list<Reflection>> indexed by reflector key and identifier type cache key */
    private $cacheByIdentifierTypeKeyAndOid = [];
    /**
     * @var \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator
     */
    private $wrappedSourceLocator;
    public function __construct(\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator $wrappedSourceLocator)
    {
        $this->wrappedSourceLocator = $wrappedSourceLocator;
    }
    public function locateIdentifier(Reflector $reflector, Identifier $identifier) : ?\PHPStan\BetterReflection\Reflection\Reflection
    {
        $cacheKey = sprintf('%s_%s', $this->reflectorCacheKey($reflector), $this->identifierToCacheKey($identifier));
        if (array_key_exists($cacheKey, $this->cacheByIdentifierKeyAndOid)) {
            return $this->cacheByIdentifierKeyAndOid[$cacheKey];
        }
        return $this->cacheByIdentifierKeyAndOid[$cacheKey] = $this->wrappedSourceLocator->locateIdentifier($reflector, $identifier);
    }
    /** @return list<Reflection> */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType) : array
    {
        $cacheKey = sprintf('%s_%s', $this->reflectorCacheKey($reflector), $this->identifierTypeToCacheKey($identifierType));
        if (array_key_exists($cacheKey, $this->cacheByIdentifierTypeKeyAndOid)) {
            return $this->cacheByIdentifierTypeKeyAndOid[$cacheKey];
        }
        return $this->cacheByIdentifierTypeKeyAndOid[$cacheKey] = $this->wrappedSourceLocator->locateIdentifiersByType($reflector, $identifierType);
    }
    private function reflectorCacheKey(Reflector $reflector) : string
    {
        return sprintf('type:%s#oid:%d', \get_class($reflector), spl_object_id($reflector));
    }
    private function identifierToCacheKey(Identifier $identifier) : string
    {
        return sprintf('%s#name:%s', $this->identifierTypeToCacheKey($identifier->getType()), $identifier->getName());
    }
    private function identifierTypeToCacheKey(IdentifierType $identifierType) : string
    {
        return sprintf('type:%s', $identifierType->getName());
    }
}
