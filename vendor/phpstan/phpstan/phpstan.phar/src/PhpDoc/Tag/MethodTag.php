<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;
/**
 * @api
 * @final
 */
class MethodTag
{
    /**
     * @var Type
     */
    private $returnType;
    /**
     * @var bool
     */
    private $isStatic;
    /**
     * @var array<string, MethodTagParameter>
     */
    private $parameters;
    /**
     * @var array<string, TemplateTag>
     */
    private $templateTags;
    /**
     * @param array<string, MethodTagParameter> $parameters
     * @param array<string, TemplateTag> $templateTags
     */
    public function __construct(Type $returnType, bool $isStatic, array $parameters, array $templateTags = [])
    {
        $this->returnType = $returnType;
        $this->isStatic = $isStatic;
        $this->parameters = $parameters;
        $this->templateTags = $templateTags;
    }
    public function getReturnType() : Type
    {
        return $this->returnType;
    }
    public function isStatic() : bool
    {
        return $this->isStatic;
    }
    /**
     * @return array<string, MethodTagParameter>
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }
    /**
     * @return array<string, TemplateTag>
     */
    public function getTemplateTags() : array
    {
        return $this->templateTags;
    }
}
