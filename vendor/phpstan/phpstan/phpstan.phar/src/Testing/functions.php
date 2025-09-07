<?php

declare (strict_types=1);
namespace PHPStan\Testing;

use PHPStan\TrinaryLogic;
/**
 * Asserts the static type of a value.
 *
 * @phpstan-pure
 * @param mixed $value
 * @return mixed
 *
 * @throws void
 */
function assertType(string $type, $value)
{
    return null;
}
/**
 * Asserts the static type of a value.
 *
 * The difference from assertType() is that it doesn't resolve
 * method/function parameter phpDocs.
 *
 * @phpstan-pure
 * @param mixed $value
 * @return mixed
 *
 * @throws void
 */
function assertNativeType(string $type, $value)
{
    return null;
}
/**
 * @phpstan-pure
 * @param mixed $variable
 * @return mixed
 *
 * @throws void
 */
function assertVariableCertainty(TrinaryLogic $certainty, $variable)
{
    return null;
}
