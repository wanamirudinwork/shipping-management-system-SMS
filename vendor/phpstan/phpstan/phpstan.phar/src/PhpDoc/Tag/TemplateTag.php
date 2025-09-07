<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\Type;
/**
 * @api
 * @final
 */
class TemplateTag
{
    /**
     * @var non-empty-string
     */
    private $name;
    /**
     * @var Type
     */
    private $bound;
    /**
     * @var ?Type
     */
    private $default;
    /**
     * @var TemplateTypeVariance
     */
    private $variance;
    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name, Type $bound, ?Type $default, TemplateTypeVariance $variance)
    {
        $this->name = $name;
        $this->bound = $bound;
        $this->default = $default;
        $this->variance = $variance;
    }
    /**
     * @return non-empty-string
     */
    public function getName() : string
    {
        return $this->name;
    }
    public function getBound() : Type
    {
        return $this->bound;
    }
    public function getDefault() : ?Type
    {
        return $this->default;
    }
    public function getVariance() : TemplateTypeVariance
    {
        return $this->variance;
    }
}
