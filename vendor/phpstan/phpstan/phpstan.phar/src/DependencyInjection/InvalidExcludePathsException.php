<?php

declare (strict_types=1);
namespace PHPStan\DependencyInjection;

use Exception;
use function implode;
final class InvalidExcludePathsException extends Exception
{
    /**
     * @var string[]
     */
    private $errors;
    /**
     * @param string[] $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct(implode("\n", $this->errors));
    }
    /**
     * @return string[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
