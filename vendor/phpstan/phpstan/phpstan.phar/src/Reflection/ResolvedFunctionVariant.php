<?php

declare (strict_types=1);
namespace PHPStan\Reflection;

use PHPStan\Type\Type;
interface ResolvedFunctionVariant extends \PHPStan\Reflection\ParametersAcceptorWithPhpDocs
{
    public function getOriginalParametersAcceptor() : \PHPStan\Reflection\ParametersAcceptor;
    public function getReturnTypeWithUnresolvableTemplateTypes() : Type;
    public function getPhpDocReturnTypeWithUnresolvableTemplateTypes() : Type;
}
