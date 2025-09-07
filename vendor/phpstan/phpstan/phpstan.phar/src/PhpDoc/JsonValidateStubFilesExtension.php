<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\Php\PhpVersion;
final class JsonValidateStubFilesExtension implements \PHPStan\PhpDoc\StubFilesExtension
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
        if (!$this->phpVersion->supportsJsonValidate()) {
            return [];
        }
        return [__DIR__ . '/../../stubs/json_validate.stub'];
    }
}
