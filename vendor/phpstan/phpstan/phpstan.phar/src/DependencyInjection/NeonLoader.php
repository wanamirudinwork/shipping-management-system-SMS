<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use _PHPStan_14faee166\Nette\DI\Config\Loader;
use PHPStan\File\FileHelper;
final class NeonLoader extends Loader
{
    /**
     * @var FileHelper
     */
    private $fileHelper;
    /**
     * @var ?string
     */
    private $generateBaselineFile;
    public function __construct(FileHelper $fileHelper, ?string $generateBaselineFile)
    {
        $this->fileHelper = $fileHelper;
        $this->generateBaselineFile = $generateBaselineFile;
    }
    /**
     * @return mixed[]
     */
    public function load(string $file, ?bool $merge = \true) : array
    {
        if ($this->generateBaselineFile === null) {
            return parent::load($file, $merge);
        }
        $normalizedFile = $this->fileHelper->normalizePath($file);
        if ($this->generateBaselineFile === $normalizedFile) {
            return [];
        }
        return parent::load($file, $merge);
    }
}
