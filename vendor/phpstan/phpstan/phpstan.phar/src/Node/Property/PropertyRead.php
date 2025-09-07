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
class PropertyRead
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
     * @param PropertyFetch|StaticPropertyFetch $fetch
     */
    public function __construct($fetch, Scope $scope)
    {
        $this->fetch = $fetch;
        $this->scope = $scope;
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
}
