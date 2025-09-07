<?php

declare (strict_types=1);
namespace PHPStan\Process;

use _PHPStan_14faee166\Fidry\CpuCoreCounter\CpuCoreCounter as FidryCpuCoreCounter;
use _PHPStan_14faee166\Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
final class CpuCoreCounter
{
    /**
     * @var ?int
     */
    private $count = null;
    public function getNumberOfCpuCores() : int
    {
        if ($this->count !== null) {
            return $this->count;
        }
        try {
            $this->count = (new FidryCpuCoreCounter())->getCount();
        } catch (NumberOfCpuCoreNotFound $e) {
            $this->count = 1;
        }
        return $this->count;
    }
}
