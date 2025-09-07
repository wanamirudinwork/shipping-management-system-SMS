<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;
/**
 * @api
 * @final
 */
final class ParamClosureThisTag implements \PHPStan\PhpDoc\Tag\TypedTag
{
    /**
     * @var Type
     */
    private $type;
    public function __construct(Type $type)
    {
        $this->type = $type;
    }
    public function getType() : Type
    {
        return $this->type;
    }
    /**
     * @return self
     */
    public function withType(Type $type) : \PHPStan\PhpDoc\Tag\TypedTag
    {
        return new self($type);
    }
}
