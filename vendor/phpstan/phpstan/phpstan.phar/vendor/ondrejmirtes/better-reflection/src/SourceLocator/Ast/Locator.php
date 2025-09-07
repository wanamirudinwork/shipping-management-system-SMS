<?php

declare (strict_types=1);
namespace PHPStan\BetterReflection\SourceLocator\Ast;

use PhpParser\Node;
use PhpParser\Parser;
use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use Throwable;
use function strtolower;
/** @internal */
class Locator
{
    /**
     * @var \PHPStan\BetterReflection\SourceLocator\Ast\FindReflectionsInTree
     */
    private $findReflectionsInTree;
    /**
     * @var \PhpParser\Parser
     */
    private $parser;
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
        $this->findReflectionsInTree = new \PHPStan\BetterReflection\SourceLocator\Ast\FindReflectionsInTree(new NodeToReflection());
    }
    /**
     * @throws IdentifierNotFound
     * @throws Exception\ParseToAstFailure
     */
    public function findReflection(Reflector $reflector, LocatedSource $locatedSource, Identifier $identifier) : Reflection
    {
        return $this->findInArray($this->findReflectionsOfType($reflector, $locatedSource, $identifier->getType()), $identifier, $locatedSource->getName());
    }
    /**
     * Get an array of reflections found in some code.
     *
     * @return list<Reflection>
     *
     * @throws Exception\ParseToAstFailure
     */
    public function findReflectionsOfType(Reflector $reflector, LocatedSource $locatedSource, IdentifierType $identifierType) : array
    {
        try {
            /** @var list<Node\Stmt> $ast */
            $ast = $this->parser->parse($locatedSource->getSource());
            return $this->findReflectionsInTree->__invoke($reflector, $ast, $identifierType, $locatedSource);
        } catch (Throwable $exception) {
            throw \PHPStan\BetterReflection\SourceLocator\Ast\Exception\ParseToAstFailure::fromLocatedSource($locatedSource, $exception);
        }
    }
    /**
     * Given an array of Reflections, try to find the identifier.
     *
     * @param list<Reflection> $reflections
     *
     * @throws IdentifierNotFound
     */
    private function findInArray(array $reflections, Identifier $identifier, ?string $name) : Reflection
    {
        if ($name === null) {
            throw IdentifierNotFound::fromIdentifier($identifier);
        }
        $identifierName = strtolower($name);
        foreach ($reflections as $reflection) {
            if (strtolower($reflection->getName()) === $identifierName) {
                return $reflection;
            }
        }
        throw IdentifierNotFound::fromIdentifier($identifier);
    }
}
