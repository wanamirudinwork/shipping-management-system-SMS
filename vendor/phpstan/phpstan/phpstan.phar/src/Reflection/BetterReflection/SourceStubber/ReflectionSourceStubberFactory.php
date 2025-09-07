<?php

declare (strict_types=1);
namespace PHPStan\Reflection\BetterReflection\SourceStubber;

use PHPStan\BetterReflection\SourceLocator\SourceStubber\ReflectionSourceStubber;
use PHPStan\Node\Printer\Printer;
use PHPStan\Php\PhpVersion;
final class ReflectionSourceStubberFactory
{
    /**
     * @var Printer
     */
    private $printer;
    /**
     * @var PhpVersion
     */
    private $phpVersion;
    public function __construct(Printer $printer, PhpVersion $phpVersion)
    {
        $this->printer = $printer;
        $this->phpVersion = $phpVersion;
    }
    public function create() : ReflectionSourceStubber
    {
        return new ReflectionSourceStubber($this->printer, $this->phpVersion->getVersionId());
    }
}
