<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
final class PhpDocStringResolver
{
    /**
     * @var Lexer
     */
    private $phpDocLexer;
    /**
     * @var PhpDocParser
     */
    private $phpDocParser;
    public function __construct(Lexer $phpDocLexer, PhpDocParser $phpDocParser)
    {
        $this->phpDocLexer = $phpDocLexer;
        $this->phpDocParser = $phpDocParser;
    }
    public function resolve(string $phpDocString) : PhpDocNode
    {
        $tokens = new TokenIterator($this->phpDocLexer->tokenize($phpDocString));
        $phpDocNode = $this->phpDocParser->parse($tokens);
        $tokens->consumeTokenType(Lexer::TOKEN_END);
        // @phpstan-ignore missingType.checkedException
        return $phpDocNode;
    }
}
