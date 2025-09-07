<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;
/**
 * @api
 * @final
 */
class PropertyTag
{
    /**
     * @var Type
     */
    private $type;
    /**
     * @var ?Type
     */
    private $readableType;
    /**
     * @var ?Type
     */
    private $writableType;
    public function __construct(Type $type, ?Type $readableType, ?Type $writableType)
    {
        $this->type = $type;
        $this->readableType = $readableType;
        $this->writableType = $writableType;
    }
    /**
     * @deprecated Use getReadableType() / getWritableType()
     */
    public function getType() : Type
    {
        return $this->type;
    }
    public function getReadableType() : ?Type
    {
        return $this->readableType;
    }
    public function getWritableType() : ?Type
    {
        return $this->writableType;
    }
    /**
     * @phpstan-assert-if-true !null $this->getReadableType()
     */
    public function isReadable() : bool
    {
        return $this->readableType !== null;
    }
    /**
     * @phpstan-assert-if-true !null $this->getWritableType()
     */
    public function isWritable() : bool
    {
        return $this->writableType !== null;
    }
}
