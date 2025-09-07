<?php

declare (strict_types=1);
namespace PHPStan\File;

final class FileFinderResult
{
    /**
     * @var string[]
     */
    private $files;
    /**
     * @var bool
     */
    private $onlyFiles;
    /**
     * @param string[] $files
     */
    public function __construct(array $files, bool $onlyFiles)
    {
        $this->files = $files;
        $this->onlyFiles = $onlyFiles;
    }
    /**
     * @return string[]
     */
    public function getFiles() : array
    {
        return $this->files;
    }
    public function isOnlyFiles() : bool
    {
        return $this->onlyFiles;
    }
}
