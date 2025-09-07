<?php

declare (strict_types=1);
namespace _PHPStan_14faee166\OndraM\CiDetector\Ci;

use _PHPStan_14faee166\OndraM\CiDetector\Env;
/**
 * Unified adapter to retrieve environment variables from current continuous integration server
 */
abstract class AbstractCi implements CiInterface
{
    /** @var Env */
    protected $env;
    public function __construct(Env $env)
    {
        $this->env = $env;
    }
}
