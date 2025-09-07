<?php

declare (strict_types=1);
namespace PHPStan\Node\Property;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Analyser\Scope;
/**
 * @api
 * @final
 */
class PropertyWrite
{
    /**
     * @var PropertyFetch|StaticPropertyFetch
     */
    private $fetch;
    /**
     * @var Scope
     */
    private $scope;
    /**
     * @var bool
     */
    private $promotedPropertyWrite;
    /**
     * @param PropertyFetch|StaticPropertyFetch $fetch
     */
    public function __construct($fetch, Scope $scope, bool $promotedPropertyWrite)
    {
        $this->fetch = $fetch;
        $this->scope = $scope;
        $this->promotedPropertyWrite = $promotedPropertyWrite;
    }
    /**
     * @return PropertyFetch|StaticPropertyFetch
     */
    public function getFetch()
    {
        return $this->fetch;
    }
    public function getScope() : Scope
    {
        return $this->scope;
    }
    public function isPromotedPropertyWrite() : bool
    {
        return $this->promotedPropertyWrite;
    }
}
