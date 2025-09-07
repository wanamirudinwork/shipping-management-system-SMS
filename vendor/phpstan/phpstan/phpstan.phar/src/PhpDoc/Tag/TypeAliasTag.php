<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\TypeAlias;
/**
 * @api
 * @final
 */
class TypeAliasTag
{
    /**
     * @var string
     */
    private $aliasName;
    /**
     * @var TypeNode
     */
    private $typeNode;
    /**
     * @var NameScope
     */
    private $nameScope;
    public function __construct(string $aliasName, TypeNode $typeNode, NameScope $nameScope)
    {
        $this->aliasName = $aliasName;
        $this->typeNode = $typeNode;
        $this->nameScope = $nameScope;
    }
    public function getAliasName() : string
    {
        return $this->aliasName;
    }
    public function getTypeAlias() : TypeAlias
    {
        return new TypeAlias($this->typeNode, $this->nameScope);
    }
}
