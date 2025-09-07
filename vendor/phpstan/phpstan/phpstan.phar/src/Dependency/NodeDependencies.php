<?php

declare (strict_types=1);
namespace PHPStan\Dependency;

use PHPStan\File\FileHelper;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use function array_values;
final class NodeDependencies
{
    /**
     * @var FileHelper
     */
    private $fileHelper;
    /**
     * @var array<int, ClassReflection|FunctionReflection>
     */
    private $reflections;
    /**
     * @var ?RootExportedNode
     */
    private $exportedNode;
    /**
     * @param array<int, ClassReflection|FunctionReflection> $reflections
     */
    public function __construct(FileHelper $fileHelper, array $reflections, ?\PHPStan\Dependency\RootExportedNode $exportedNode)
    {
        $this->fileHelper = $fileHelper;
        $this->reflections = $reflections;
        $this->exportedNode = $exportedNode;
    }
    /**
     * @param array<string, true> $analysedFiles
     * @return string[]
     */
    public function getFileDependencies(string $currentFile, array $analysedFiles) : array
    {
        $dependencies = [];
        foreach ($this->reflections as $dependencyReflection) {
            $dependencyFile = $dependencyReflection->getFileName();
            if ($dependencyFile === null) {
                continue;
            }
            $dependencyFile = $this->fileHelper->normalizePath($dependencyFile);
            if ($currentFile === $dependencyFile) {
                continue;
            }
            if (!isset($analysedFiles[$dependencyFile])) {
                continue;
            }
            $dependencies[$dependencyFile] = $dependencyFile;
        }
        return array_values($dependencies);
    }
    public function getExportedNode() : ?\PHPStan\Dependency\RootExportedNode
    {
        return $this->exportedNode;
    }
}
