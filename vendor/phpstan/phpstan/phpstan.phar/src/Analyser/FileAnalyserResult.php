<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\Collectors\CollectedData;
use PHPStan\Dependency\RootExportedNode;
/**
 * @phpstan-type LinesToIgnore = array<string, array<int, non-empty-list<string>|null>>
 */
final class FileAnalyserResult
{
    /**
     * @var list<Error>
     */
    private $errors;
    /**
     * @var list<Error>
     */
    private $filteredPhpErrors;
    /**
     * @var list<Error>
     */
    private $allPhpErrors;
    /**
     * @var list<Error>
     */
    private $locallyIgnoredErrors;
    /**
     * @var list<CollectedData>
     */
    private $collectedData;
    /**
     * @var list<string>
     */
    private $dependencies;
    /**
     * @var list<RootExportedNode>
     */
    private $exportedNodes;
    /**
     * @var LinesToIgnore
     */
    private $linesToIgnore;
    /**
     * @var LinesToIgnore
     */
    private $unmatchedLineIgnores;
    /**
     * @param list<Error> $errors
     * @param list<Error> $filteredPhpErrors
     * @param list<Error> $allPhpErrors
     * @param list<Error> $locallyIgnoredErrors
     * @param list<CollectedData> $collectedData
     * @param list<string> $dependencies
     * @param list<RootExportedNode> $exportedNodes
     * @param LinesToIgnore $linesToIgnore
     * @param LinesToIgnore $unmatchedLineIgnores
     */
    public function __construct(array $errors, array $filteredPhpErrors, array $allPhpErrors, array $locallyIgnoredErrors, array $collectedData, array $dependencies, array $exportedNodes, array $linesToIgnore, array $unmatchedLineIgnores)
    {
        $this->errors = $errors;
        $this->filteredPhpErrors = $filteredPhpErrors;
        $this->allPhpErrors = $allPhpErrors;
        $this->locallyIgnoredErrors = $locallyIgnoredErrors;
        $this->collectedData = $collectedData;
        $this->dependencies = $dependencies;
        $this->exportedNodes = $exportedNodes;
        $this->linesToIgnore = $linesToIgnore;
        $this->unmatchedLineIgnores = $unmatchedLineIgnores;
    }
    /**
     * @return list<Error>
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
    /**
     * @return list<Error>
     */
    public function getFilteredPhpErrors() : array
    {
        return $this->filteredPhpErrors;
    }
    /**
     * @return list<Error>
     */
    public function getAllPhpErrors() : array
    {
        return $this->allPhpErrors;
    }
    /**
     * @return list<Error>
     */
    public function getLocallyIgnoredErrors() : array
    {
        return $this->locallyIgnoredErrors;
    }
    /**
     * @return list<CollectedData>
     */
    public function getCollectedData() : array
    {
        return $this->collectedData;
    }
    /**
     * @return list<string>
     */
    public function getDependencies() : array
    {
        return $this->dependencies;
    }
    /**
     * @return list<RootExportedNode>
     */
    public function getExportedNodes() : array
    {
        return $this->exportedNodes;
    }
    /**
     * @return LinesToIgnore
     */
    public function getLinesToIgnore() : array
    {
        return $this->linesToIgnore;
    }
    /**
     * @return LinesToIgnore
     */
    public function getUnmatchedLineIgnores() : array
    {
        return $this->unmatchedLineIgnores;
    }
}
