<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

/**
 * @phpstan-import-type LinesToIgnore from FileAnalyserResult
 */
final class LocalIgnoresProcessorResult
{
    /**
     * @var list<Error>
     */
    private $fileErrors;
    /**
     * @var list<Error>
     */
    private $locallyIgnoredErrors;
    /**
     * @var LinesToIgnore
     */
    private $linesToIgnore;
    /**
     * @var LinesToIgnore
     */
    private $unmatchedLineIgnores;
    /**
     * @param list<Error> $fileErrors
     * @param list<Error> $locallyIgnoredErrors
     * @param LinesToIgnore $linesToIgnore
     * @param LinesToIgnore $unmatchedLineIgnores
     */
    public function __construct(array $fileErrors, array $locallyIgnoredErrors, array $linesToIgnore, array $unmatchedLineIgnores)
    {
        $this->fileErrors = $fileErrors;
        $this->locallyIgnoredErrors = $locallyIgnoredErrors;
        $this->linesToIgnore = $linesToIgnore;
        $this->unmatchedLineIgnores = $unmatchedLineIgnores;
    }
    /**
     * @return list<Error>
     */
    public function getFileErrors() : array
    {
        return $this->fileErrors;
    }
    /**
     * @return list<Error>
     */
    public function getLocallyIgnoredErrors() : array
    {
        return $this->locallyIgnoredErrors;
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
