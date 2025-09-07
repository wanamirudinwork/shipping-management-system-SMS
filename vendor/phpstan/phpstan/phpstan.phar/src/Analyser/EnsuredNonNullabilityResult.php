<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

final class EnsuredNonNullabilityResult
{
    /**
     * @var MutatingScope
     */
    private $scope;
    /**
     * @var EnsuredNonNullabilityResultExpression[]
     */
    private $specifiedExpressions;
    /**
     * @param EnsuredNonNullabilityResultExpression[] $specifiedExpressions
     */
    public function __construct(\PHPStan\Analyser\MutatingScope $scope, array $specifiedExpressions)
    {
        $this->scope = $scope;
        $this->specifiedExpressions = $specifiedExpressions;
    }
    public function getScope() : \PHPStan\Analyser\MutatingScope
    {
        return $this->scope;
    }
    /**
     * @return EnsuredNonNullabilityResultExpression[]
     */
    public function getSpecifiedExpressions() : array
    {
        return $this->specifiedExpressions;
    }
}
