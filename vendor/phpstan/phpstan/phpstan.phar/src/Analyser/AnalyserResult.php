<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

use PHPStan\Collectors\CollectedData;
use PHPStan\Dependency\RootExportedNode;
use function usort;
/**
 * @phpstan-import-type LinesToIgnore from FileAnalyserResult
 */
final class AnalyserResult
{
    /**
     * @var list<Error>
     */
    private $unorderedErrors;
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
     * @var array<string, LinesToIgnore>
     */
    private $linesToIgnore;
    /**
     * @var array<string, LinesToIgnore>
     */
    private $unmatchedLineIgnores;
    /**
     * @var list<InternalError>
     */
    private $internalErrors;
    /**
     * @var list<CollectedData>
     */
    private $collectedData;
    /**
     * @var array<string, array<string>>|null
     */
    private $dependencies;
    /**
     * @var array<string, array<RootExportedNode>>
     */
    private $exportedNodes;
    /**
     * @var bool
     */
    private $reachedInternalErrorsCountLimit;
    /**
     * @var int
     */
    private $peakMemoryUsageBytes;
    /** @var list<Error>|null */
    private $errors = null;
    /**
     * @param list<Error> $unorderedErrors
     * @param list<Error> $filteredPhpErrors
     * @param list<Error> $allPhpErrors
     * @param list<Error> $locallyIgnoredErrors
     * @param array<string, LinesToIgnore> $linesToIgnore
     * @param array<string, LinesToIgnore> $unmatchedLineIgnores
     * @param list<CollectedData> $collectedData
     * @param list<InternalError> $internalErrors
     * @param array<string, array<string>>|null $dependencies
     * @param array<string, array<RootExportedNode>> $exportedNodes
     */
    public function __construct(array $unorderedErrors, array $filteredPhpErrors, array $allPhpErrors, array $locallyIgnoredErrors, array $linesToIgnore, array $unmatchedLineIgnores, array $internalErrors, array $collectedData, ?array $dependencies, array $exportedNodes, bool $reachedInternalErrorsCountLimit, int $peakMemoryUsageBytes)
    {
        $this->unorderedErrors = $unorderedErrors;
        $this->filteredPhpErrors = $filteredPhpErrors;
        $this->allPhpErrors = $allPhpErrors;
        $this->locallyIgnoredErrors = $locallyIgnoredErrors;
        $this->linesToIgnore = $linesToIgnore;
        $this->unmatchedLineIgnores = $unmatchedLineIgnores;
        $this->internalErrors = $internalErrors;
        $this->collectedData = $collectedData;
        $this->dependencies = $dependencies;
        $this->exportedNodes = $exportedNodes;
        $this->reachedInternalErrorsCountLimit = $reachedInternalErrorsCountLimit;
        $this->peakMemoryUsageBytes = $peakMemoryUsageBytes;
    }
    /**
     * @return list<Error>
     */
    public function getUnorderedErrors() : array
    {
        return $this->unorderedErrors;
    }
    /**
     * @return list<Error>
     */
    public function getErrors() : array
    {
        if (!isset($this->errors)) {
            $this->errors = $this->unorderedErrors;
            usort($this->errors, static function (\PHPStan\Analyser\Error $a, \PHPStan\Analyser\Error $b) : int {
                return [$a->getFile(), $a->getLine(), $a->getMessage()] <=> [$b->getFile(), $b->getLine(), $b->getMessage()];
            });
        }
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
     * @return array<string, LinesToIgnore>
     */
    public function getLinesToIgnore() : array
    {
        return $this->linesToIgnore;
    }
    /**
     * @return array<string, LinesToIgnore>
     */
    public function getUnmatchedLineIgnores() : array
    {
        return $this->unmatchedLineIgnores;
    }
    /**
     * @return list<InternalError>
     */
    public function getInternalErrors() : array
    {
        return $this->internalErrors;
    }
    /**
     * @return list<CollectedData>
     */
    public function getCollectedData() : array
    {
        return $this->collectedData;
    }
    /**
     * @return array<string, array<string>>|null
     */
    public function getDependencies() : ?array
    {
        return $this->dependencies;
    }
    /**
     * @return array<string, array<RootExportedNode>>
     */
    public function getExportedNodes() : array
    {
        return $this->exportedNodes;
    }
    public function hasReachedInternalErrorsCountLimit() : bool
    {
        return $this->reachedInternalErrorsCountLimit;
    }
    public function getPeakMemoryUsageBytes() : int
    {
        return $this->peakMemoryUsageBytes;
    }
}
