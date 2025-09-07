<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use _PHPStan_14faee166\Nette\DI\CompilerExtension;
use _PHPStan_14faee166\Nette\DI\Definitions\Statement;
use _PHPStan_14faee166\Nette\Schema\Expect;
use _PHPStan_14faee166\Nette\Schema\Schema;
final class ParametersSchemaExtension extends CompilerExtension
{
    public function getConfigSchema() : Schema
    {
        return Expect::arrayOf(Expect::type(Statement::class))->min(1);
    }
}
