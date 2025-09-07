<?php

declare (strict_types=1);
namespace PHPStan\Parser;

use PhpParser\Error;
use PhpParser\ErrorHandler;
use PhpParser\Node;
use PhpParser\Parser;
use function sprintf;
final class PhpParserDecorator implements Parser
{
    /**
     * @var \PHPStan\Parser\Parser
     */
    private $wrappedParser;
    public function __construct(\PHPStan\Parser\Parser $wrappedParser)
    {
        $this->wrappedParser = $wrappedParser;
    }
    /**
     * @return Node\Stmt[]
     */
    public function parse(string $code, ?ErrorHandler $errorHandler = null) : array
    {
        try {
            return $this->wrappedParser->parseString($code);
        } catch (\PHPStan\Parser\ParserErrorsException $e) {
            $message = $e->getMessage();
            if ($e->getParsedFile() !== null) {
                $message .= sprintf(' in file %s', $e->getParsedFile());
            }
            throw new Error($message, $e->getAttributes());
        }
    }
}
