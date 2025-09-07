<?php

declare (strict_types=1);
namespace PHPStan\Analyser;

/**
 * @api
 * @final
 */
class ScopeFactory
{
    /**
     * @var InternalScopeFactory
     */
    private $internalScopeFactory;
    public function __construct(\PHPStan\Analyser\InternalScopeFactory $internalScopeFactory)
    {
        $this->internalScopeFactory = $internalScopeFactory;
    }
    public function create(\PHPStan\Analyser\ScopeContext $context) : \PHPStan\Analyser\MutatingScope
    {
        return $this->internalScopeFactory->create($context);
    }
}
