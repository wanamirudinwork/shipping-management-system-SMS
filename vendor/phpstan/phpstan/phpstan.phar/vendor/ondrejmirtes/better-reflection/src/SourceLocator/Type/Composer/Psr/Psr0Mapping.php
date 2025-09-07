<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr;

use PHPStan\BetterReflection\Identifier\Identifier;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function rtrim;
use function str_replace;
use function strpos;
final class Psr0Mapping implements \PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\PsrAutoloaderMapping
{
    /** @var array<string, list<string>> */
    private $mappings = [];
    private function __construct()
    {
    }
    /** @param array<string, list<string>> $mappings */
    public static function fromArrayMappings(array $mappings) : self
    {
        $instance = new self();
        $instance->mappings = array_map(static function (array $directories) : array {
            return array_map(static function (string $directory) : string {
                return rtrim($directory, '/');
            }, $directories);
        }, $mappings);
        return $instance;
    }
    /** {@inheritDoc} */
    public function resolvePossibleFilePaths(Identifier $identifier) : array
    {
        if (!$identifier->isClass()) {
            return [];
        }
        $className = $identifier->getName();
        foreach ($this->mappings as $prefix => $paths) {
            if ($prefix === '') {
                continue;
            }
            if (strpos($className, $prefix) === 0) {
                return array_map(static function (string $path) use($className) : string {
                    return $path . '/' . str_replace(['\\', '_'], '/', $className) . '.php';
                }, $paths);
            }
        }
        return [];
    }
    /** {@inheritDoc} */
    public function directories() : array
    {
        return array_values(array_unique(array_merge([], ...array_values($this->mappings))));
    }
}
