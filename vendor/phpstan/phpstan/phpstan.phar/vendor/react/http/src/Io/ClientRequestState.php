<?php

namespace _PHPStan_14faee166\React\Http\Io;

/** @internal */
class ClientRequestState
{
    /** @var int */
    public $numRequests = 0;
    /** @var ?\React\Promise\PromiseInterface */
    public $pending = null;
    /** @var ?\React\EventLoop\TimerInterface */
    public $timeout = null;
}
