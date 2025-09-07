<?php

declare (strict_types=1);
namespace PHPStan\Internal;

use _PHPStan_14faee166\Nette\Utils\Json;
use _PHPStan_14faee166\Nette\Utils\JsonException;
use PHPStan\File\CouldNotReadFileException;
use PHPStan\File\FileReader;
use function basename;
use function getenv;
use function is_file;
use function is_string;
use function preg_match;
use function substr;
use function trim;
final class ComposerHelper
{
    public const UNKNOWN_VERSION = 'Unknown version';
    /**
     * @var ?string
     */
    private static $phpstanVersion = null;
    /** @return array<string, mixed>|null */
    public static function getComposerConfig(string $root) : ?array
    {
        $composerJsonPath = self::getComposerJsonPath($root);
        if (!is_file($composerJsonPath)) {
            return null;
        }
        try {
            $composerJsonContents = FileReader::read($composerJsonPath);
            return Json::decode($composerJsonContents, Json::FORCE_ARRAY);
        } catch (CouldNotReadFileException|JsonException $e) {
            return null;
        }
    }
    private static function getComposerJsonPath(string $root) : string
    {
        $envComposer = getenv('COMPOSER');
        $fileName = is_string($envComposer) ? $envComposer : 'composer.json';
        $fileName = basename(trim($fileName));
        return $root . '/' . $fileName;
    }
    /**
     * @param array<string, mixed> $composerConfig
     */
    public static function getVendorDirFromComposerConfig(string $root, array $composerConfig) : string
    {
        $vendorDirectory = $composerConfig['config']['vendor-dir'] ?? 'vendor';
        return $root . '/' . trim($vendorDirectory, '/');
    }
    /**
     * @param array<string, mixed> $composerConfig
     */
    public static function getBinDirFromComposerConfig(string $root, array $composerConfig) : string
    {
        $vendorDirectory = $composerConfig['config']['bin-dir'] ?? 'vendor/bin';
        return $root . '/' . trim($vendorDirectory, '/');
    }
    public static function getPhpStanVersion() : string
    {
        if (self::$phpstanVersion !== null) {
            return self::$phpstanVersion;
        }
        $installed = (require __DIR__ . '/../../vendor/composer/installed.php');
        $rootPackage = $installed['root'] ?? null;
        if ($rootPackage === null) {
            return self::$phpstanVersion = self::UNKNOWN_VERSION;
        }
        if (preg_match('/[^v\\d.]/', $rootPackage['pretty_version']) === 0) {
            // Handles tagged versions, see https://github.com/Jean85/pretty-package-versions/blob/2.0.5/src/Version.php#L31
            return self::$phpstanVersion = $rootPackage['pretty_version'];
        }
        return self::$phpstanVersion = $rootPackage['pretty_version'] . '@' . substr((string) $rootPackage['reference'], 0, 7);
    }
}
