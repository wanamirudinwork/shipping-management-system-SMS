<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\Php\PhpVersion;
final class ReflectionEnumStubFilesExtension implements \PHPStan\PhpDoc\StubFilesExtension
{
    /**
     * @var PhpVersion
     */
    private $phpVersion;
    public function __construct(PhpVersion $phpVersion)
    {
        $this->phpVersion = $phpVersion;
    }
    public function getFiles() : array
    {
        if (!$this->phpVersion->supportsEnums()) {
            return [];
        }
        return [__DIR__ . '/../../stubs/ReflectionEnum.stub'];
    }
}
