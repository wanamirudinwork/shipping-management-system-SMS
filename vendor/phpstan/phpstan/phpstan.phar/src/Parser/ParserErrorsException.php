<?php

declare (strict_types=1);
namespace PHPStan\Parser;

use Exception;
use PhpParser\Error;
use function array_map;
use function count;
use function implode;
final class ParserErrorsException extends Exception
{
    /**
     * @var Error[]
     */
    private $errors;
    /**
     * @var ?string
     */
    private $parsedFile;
    /** @var mixed[] */
    private $attributes;
    /**
     * @param Error[] $errors
     */
    public function __construct(array $errors, ?string $parsedFile)
    {
        $this->errors = $errors;
        $this->parsedFile = $parsedFile;
        parent::__construct(implode(', ', array_map(static function (Error $error) : string {
            return $error->getRawMessage();
        }, $errors)));
        if (count($errors) > 0) {
            $this->attributes = $errors[0]->getAttributes();
        } else {
            $this->attributes = [];
        }
    }
    /**
     * @return Error[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
    public function getParsedFile() : ?string
    {
        return $this->parsedFile;
    }
    /**
     * @return mixed[]
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }
}
