<?php

declare (strict_types=1);
namespace PHPStan\Parallel;

use PHPStan\ShouldNotHappenException;
use _PHPStan_14faee166\React\EventLoop\LoopInterface;
use _PHPStan_14faee166\React\EventLoop\TimerInterface;
use _PHPStan_14faee166\React\Stream\ReadableStreamInterface;
use _PHPStan_14faee166\React\Stream\WritableStreamInterface;
use Throwable;
use function fclose;
use function is_string;
use function rewind;
use function sprintf;
use function stream_get_contents;
use function tmpfile;
final class Process
{
    /**
     * @var string
     */
    private $command;
    /**
     * @var LoopInterface
     */
    private $loop;
    /**
     * @var float
     */
    private $timeoutSeconds;
    /**
     * @var \React\ChildProcess\Process
     */
    public $process;
    /**
     * @var ?WritableStreamInterface
     */
    private $in = null;
    /** @var resource */
    private $stdOut;
    /** @var resource */
    private $stdErr;
    /** @var callable(mixed[] $json) : void */
    private $onData;
    /** @var callable(Throwable $exception): void */
    private $onError;
    /**
     * @var ?TimerInterface
     */
    private $timer = null;
    public function __construct(string $command, LoopInterface $loop, float $timeoutSeconds)
    {
        $this->command = $command;
        $this->loop = $loop;
        $this->timeoutSeconds = $timeoutSeconds;
    }
    /**
     * @param callable(mixed[] $json) : void $onData
     * @param callable(Throwable $exception): void $onError
     * @param callable(?int $exitCode, string $output) : void $onExit
     */
    public function start(callable $onData, callable $onError, callable $onExit) : void
    {
        $tmpStdOut = tmpfile();
        if ($tmpStdOut === \false) {
            throw new ShouldNotHappenException('Failed creating temp file for stdout.');
        }
        $tmpStdErr = tmpfile();
        if ($tmpStdErr === \false) {
            throw new ShouldNotHappenException('Failed creating temp file for stderr.');
        }
        $this->stdOut = $tmpStdOut;
        $this->stdErr = $tmpStdErr;
        $this->process = new \_PHPStan_14faee166\React\ChildProcess\Process($this->command, null, null, [1 => $this->stdOut, 2 => $this->stdErr]);
        $this->process->start($this->loop);
        $this->onData = $onData;
        $this->onError = $onError;
        $this->process->on('exit', function ($exitCode) use($onExit) : void {
            $this->cancelTimer();
            $output = '';
            rewind($this->stdOut);
            $stdOut = stream_get_contents($this->stdOut);
            if (is_string($stdOut)) {
                $output .= $stdOut;
            }
            rewind($this->stdErr);
            $stdErr = stream_get_contents($this->stdErr);
            if (is_string($stdErr)) {
                $output .= $stdErr;
            }
            $onExit($exitCode, $output);
            fclose($this->stdOut);
            fclose($this->stdErr);
        });
    }
    private function cancelTimer() : void
    {
        if ($this->timer === null) {
            return;
        }
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
    }
    /**
     * @param mixed[] $data
     */
    public function request(array $data) : void
    {
        $this->cancelTimer();
        if ($this->in === null) {
            throw new ShouldNotHappenException();
        }
        $this->in->write($data);
        $this->timer = $this->loop->addTimer($this->timeoutSeconds, function () : void {
            $onError = $this->onError;
            $onError(new \PHPStan\Parallel\ProcessTimedOutException(sprintf('Child process timed out after %.1f seconds. Try making it longer with parallel.processTimeout setting.', $this->timeoutSeconds)));
        });
    }
    public function quit() : void
    {
        $this->cancelTimer();
        if (!$this->process->isRunning()) {
            return;
        }
        foreach ($this->process->pipes as $pipe) {
            $pipe->close();
        }
        if ($this->in === null) {
            return;
        }
        $this->in->end();
    }
    public function bindConnection(ReadableStreamInterface $out, WritableStreamInterface $in) : void
    {
        $out->on('data', function (array $json) : void {
            $this->cancelTimer();
            if ($json['action'] !== 'result') {
                return;
            }
            $onData = $this->onData;
            $onData($json['result']);
        });
        $this->in = $in;
        $out->on('error', function (Throwable $error) : void {
            $onError = $this->onError;
            $onError($error);
        });
        $in->on('error', function (Throwable $error) : void {
            $onError = $this->onError;
            $onError($error);
        });
    }
}
