<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\Type\Type;
final class TypeStringResolver
{
    /**
     * @var Lexer
     */
    private $typeLexer;
    /**
     * @var TypeParser
     */
    private $typeParser;
    /**
     * @var TypeNodeResolver
     */
    private $typeNodeResolver;
    public function __construct(Lexer $typeLexer, TypeParser $typeParser, \PHPStan\PhpDoc\TypeNodeResolver $typeNodeResolver)
    {
        $this->typeLexer = $typeLexer;
        $this->typeParser = $typeParser;
        $this->typeNodeResolver = $typeNodeResolver;
    }
    /** @api */
    public function resolve(string $typeString, ?NameScope $nameScope = null) : Type
    {
        $tokens = new TokenIterator($this->typeLexer->tokenize($typeString));
        $typeNode = $this->typeParser->parse($tokens);
        $tokens->consumeTokenType(Lexer::TOKEN_END);
        // @phpstan-ignore missingType.checkedException
        return $this->typeNodeResolver->resolve($typeNode, $nameScope ?? new NameScope(null, []));
    }
}
