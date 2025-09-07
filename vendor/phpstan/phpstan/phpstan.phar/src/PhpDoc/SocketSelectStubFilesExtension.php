<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\Php\PhpVersion;
final class SocketSelectStubFilesExtension implements \PHPStan\PhpDoc\StubFilesExtension
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
        if ($this->phpVersion->getVersionId() >= 80000) {
            return [__DIR__ . '/../../stubs/socket_select_php8.stub'];
        }
        return [__DIR__ . '/../../stubs/socket_select.stub'];
    }
}
