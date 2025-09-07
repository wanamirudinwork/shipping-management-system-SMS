<?php

declare (strict_types=1);
namespace PHPStan\Parallel;

use PHPStan\Command\Output;
use PHPStan\Diagnose\DiagnoseExtension;
use function array_chunk;
use function count;
use function floor;
use function max;
use function min;
use function sprintf;
final class Scheduler implements DiagnoseExtension
{
    /**
     * @var positive-int
     */
    private $jobSize;
    /**
     * @var positive-int
     */
    private $maximumNumberOfProcesses;
    /**
     * @var positive-int
     */
    private $minimumNumberOfJobsPerProcess;
    /** @var array{int, int, int, int}|null */
    private $storedData = null;
    /**
     * @param positive-int $jobSize
     * @param positive-int $maximumNumberOfProcesses
     * @param positive-int $minimumNumberOfJobsPerProcess
     */
    public function __construct(int $jobSize, int $maximumNumberOfProcesses, int $minimumNumberOfJobsPerProcess)
    {
        $this->jobSize = $jobSize;
        $this->maximumNumberOfProcesses = $maximumNumberOfProcesses;
        $this->minimumNumberOfJobsPerProcess = $minimumNumberOfJobsPerProcess;
    }
    /**
     * @param array<string> $files
     */
    public function scheduleWork(int $cpuCores, array $files) : \PHPStan\Parallel\Schedule
    {
        $jobs = array_chunk($files, $this->jobSize);
        $numberOfProcesses = min(max((int) floor(count($jobs) / $this->minimumNumberOfJobsPerProcess), 1), $cpuCores);
        $usedNumberOfProcesses = min($numberOfProcesses, $this->maximumNumberOfProcesses);
        $this->storedData = [$cpuCores, count($files), count($jobs), $usedNumberOfProcesses];
        return new \PHPStan\Parallel\Schedule($usedNumberOfProcesses, $jobs);
    }
    public function print(Output $output) : void
    {
        if ($this->storedData === null) {
            return;
        }
        [$cpuCores, $filesCount, $jobsCount, $usedNumberOfProcesses] = $this->storedData;
        $output->writeLineFormatted('<info>Parallel processing scheduler:</info>');
        $output->writeLineFormatted(sprintf('# of detected CPU %s:  %s%d', $cpuCores === 1 ? 'core' : 'cores', $cpuCores === 1 ? '' : ' ', $cpuCores));
        $output->writeLineFormatted(sprintf('# of analysed files:       %d', $filesCount));
        $output->writeLineFormatted(sprintf('# of jobs:                 %d', $jobsCount));
        $output->writeLineFormatted(sprintf('# of spawned processes:    %d', $usedNumberOfProcesses));
        $output->writeLineFormatted('');
    }
}
