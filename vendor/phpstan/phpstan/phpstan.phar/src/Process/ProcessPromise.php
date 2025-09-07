<?php

declare (strict_types=1);
namespace PHPStan\Process;

use PHPStan\ShouldNotHappenException;
use _PHPStan_14faee166\React\ChildProcess\Process;
use _PHPStan_14faee166\React\EventLoop\LoopInterface;
use _PHPStan_14faee166\React\Promise\Deferred;
use _PHPStan_14faee166\React\Promise\PromiseInterface;
use function fclose;
use function rewind;
use function stream_get_contents;
use function tmpfile;
final class ProcessPromise
{
    /**
     * @var LoopInterface
     */
    private $loop;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $command;
    /** @var Deferred<string> */
    private $deferred;
    /**
     * @var ?Process
     */
    private $process = null;
    /**
     * @var bool
     */
    private $canceled = \false;
    public function __construct(LoopInterface $loop, string $name, string $command)
    {
        $this->loop = $loop;
        $this->name = $name;
        $this->command = $command;
        $this->deferred = new Deferred();
    }
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * @return PromiseInterface<string>
     */
    public function run() : PromiseInterface
    {
        $tmpStdOutResource = tmpfile();
        if ($tmpStdOutResource === \false) {
            throw new ShouldNotHappenException('Failed creating temp file for stdout.');
        }
        $tmpStdErrResource = tmpfile();
        if ($tmpStdErrResource === \false) {
            throw new ShouldNotHappenException('Failed creating temp file for stderr.');
        }
        $this->process = new Process($this->command, null, null, [1 => $tmpStdOutResource, 2 => $tmpStdErrResource]);
        $this->process->start($this->loop);
        $this->process->on('exit', function ($exitCode) use($tmpStdOutResource, $tmpStdErrResource) : void {
            if ($this->canceled) {
                fclose($tmpStdOutResource);
                fclose($tmpStdErrResource);
                return;
            }
            rewind($tmpStdOutResource);
            $stdOut = stream_get_contents($tmpStdOutResource);
            fclose($tmpStdOutResource);
            rewind($tmpStdErrResource);
            $stdErr = stream_get_contents($tmpStdErrResource);
            fclose($tmpStdErrResource);
            if ($exitCode === null) {
                $this->deferred->reject(new \PHPStan\Process\ProcessCrashedException($stdOut . $stdErr));
                return;
            }
            if ($exitCode === 0) {
                if ($stdOut === \false) {
                    $stdOut = '';
                }
                $this->deferred->resolve($stdOut);
                return;
            }
            $this->deferred->reject(new \PHPStan\Process\ProcessCrashedException($stdOut . $stdErr));
        });
        return $this->deferred->promise();
    }
    public function cancel() : void
    {
        if ($this->process === null) {
            throw new ShouldNotHappenException('Cancelling process before running');
        }
        $this->canceled = \true;
        $this->process->terminate();
        $this->deferred->reject(new \PHPStan\Process\ProcessCanceledException());
    }
}
