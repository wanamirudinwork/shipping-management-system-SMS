<?php

declare (strict_types=1);
namespace PHPStan\Dependency;

use PhpParser\NodeTraverser;
use PHPStan\Parser\Parser;
use PHPStan\Parser\ParserErrorsException;
final class ExportedNodeFetcher
{
    /**
     * @var Parser
     */
    private $parser;
    /**
     * @var ExportedNodeVisitor
     */
    private $visitor;
    public function __construct(Parser $parser, \PHPStan\Dependency\ExportedNodeVisitor $visitor)
    {
        $this->parser = $parser;
        $this->visitor = $visitor;
    }
    /**
     * @return RootExportedNode[]
     */
    public function fetchNodes(string $fileName) : array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->visitor);
        try {
            $ast = $this->parser->parseFile($fileName);
        } catch (ParserErrorsException $e) {
            return [];
        }
        $this->visitor->reset($fileName);
        $nodeTraverser->traverse($ast);
        return $this->visitor->getExportedNodes();
    }
}
