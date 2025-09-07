<?php

declare (strict_types=1);
namespace PHPStan\Reflection\BetterReflection\SourceLocator;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\PsrAutoloaderMapping;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use function is_file;
final class OptimizedPsrAutoloaderLocator implements SourceLocator
{
    /**
     * @var PsrAutoloaderMapping
     */
    private $mapping;
    /**
     * @var OptimizedSingleFileSourceLocatorRepository
     */
    private $optimizedSingleFileSourceLocatorRepository;
    /** @var array<string, OptimizedSingleFileSourceLocator> */
    private $locators = [];
    public function __construct(PsrAutoloaderMapping $mapping, \PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorRepository $optimizedSingleFileSourceLocatorRepository)
    {
        $this->mapping = $mapping;
        $this->optimizedSingleFileSourceLocatorRepository = $optimizedSingleFileSourceLocatorRepository;
    }
    public function locateIdentifier(Reflector $reflector, Identifier $identifier) : ?Reflection
    {
        foreach ($this->locators as $locator) {
            $reflection = $locator->locateIdentifier($reflector, $identifier);
            if ($reflection === null) {
                continue;
            }
            return $reflection;
        }
        foreach ($this->mapping->resolvePossibleFilePaths($identifier) as $file) {
            if (!is_file($file)) {
                continue;
            }
            $locator = $this->optimizedSingleFileSourceLocatorRepository->getOrCreate($file);
            $reflection = $locator->locateIdentifier($reflector, $identifier);
            if ($reflection === null) {
                continue;
            }
            $this->locators[$file] = $locator;
            return $reflection;
        }
        return null;
    }
    /**
     * @return list<Reflection>
     */
    public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType) : array
    {
        return [];
    }
}
