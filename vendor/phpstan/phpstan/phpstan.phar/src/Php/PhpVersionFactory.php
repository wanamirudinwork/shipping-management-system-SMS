<?php

declare (strict_types=1);
namespace PHPStan\Php;

use function explode;
use function max;
use function min;
use const PHP_VERSION_ID;
final class PhpVersionFactory
{
    /**
     * @var ?int
     */
    private $versionId;
    /**
     * @var ?string
     */
    private $composerPhpVersion;
    public function __construct(?int $versionId, ?string $composerPhpVersion)
    {
        $this->versionId = $versionId;
        $this->composerPhpVersion = $composerPhpVersion;
    }
    public function create() : \PHPStan\Php\PhpVersion
    {
        $versionId = $this->versionId;
        if ($versionId !== null) {
            $source = \PHPStan\Php\PhpVersion::SOURCE_CONFIG;
        } elseif ($this->composerPhpVersion !== null) {
            $parts = explode('.', $this->composerPhpVersion);
            $tmp = (int) $parts[0] * 10000 + (int) ($parts[1] ?? 0) * 100 + (int) ($parts[2] ?? 0);
            $tmp = max($tmp, 70100);
            $versionId = min($tmp, 80499);
            $source = \PHPStan\Php\PhpVersion::SOURCE_COMPOSER_PLATFORM_PHP;
        } else {
            $versionId = PHP_VERSION_ID;
            $source = \PHPStan\Php\PhpVersion::SOURCE_RUNTIME;
        }
        return new \PHPStan\Php\PhpVersion($versionId, $source);
    }
}
