<?php

declare (strict_types=1);
namespace PHPStan\PhpDoc;

use PhpParser\Parser;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\SourceStubber\PhpStormStubsSourceStubber;
use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\Composer\Psr\Psr4Mapping;
use PHPStan\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedPsrAutoloaderLocatorFactory;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocatorRepository;
use function dirname;
final class StubSourceLocatorFactory
{
    /**
     * @var Parser
     */
    private $php8Parser;
    /**
     * @var PhpStormStubsSourceStubber
     */
    private $phpStormStubsSourceStubber;
    /**
     * @var OptimizedSingleFileSourceLocatorRepository
     */
    private $optimizedSingleFileSourceLocatorRepository;
    /**
     * @var OptimizedPsrAutoloaderLocatorFactory
     */
    private $optimizedPsrAutoloaderLocatorFactory;
    /**
     * @var StubFilesProvider
     */
    private $stubFilesProvider;
    public function __construct(Parser $php8Parser, PhpStormStubsSourceStubber $phpStormStubsSourceStubber, OptimizedSingleFileSourceLocatorRepository $optimizedSingleFileSourceLocatorRepository, OptimizedPsrAutoloaderLocatorFactory $optimizedPsrAutoloaderLocatorFactory, \PHPStan\PhpDoc\StubFilesProvider $stubFilesProvider)
    {
        $this->php8Parser = $php8Parser;
        $this->phpStormStubsSourceStubber = $phpStormStubsSourceStubber;
        $this->optimizedSingleFileSourceLocatorRepository = $optimizedSingleFileSourceLocatorRepository;
        $this->optimizedPsrAutoloaderLocatorFactory = $optimizedPsrAutoloaderLocatorFactory;
        $this->stubFilesProvider = $stubFilesProvider;
    }
    public function create() : SourceLocator
    {
        $locators = [];
        $astPhp8Locator = new Locator($this->php8Parser);
        foreach ($this->stubFilesProvider->getStubFiles() as $stubFile) {
            $locators[] = $this->optimizedSingleFileSourceLocatorRepository->getOrCreate($stubFile);
        }
        $locators[] = $this->optimizedPsrAutoloaderLocatorFactory->create(Psr4Mapping::fromArrayMappings(['PHPStan\\' => [dirname(__DIR__) . '/']]));
        $locators[] = $this->optimizedPsrAutoloaderLocatorFactory->create(Psr4Mapping::fromArrayMappings(['PhpParser\\' => [dirname(__DIR__, 2) . '/vendor/nikic/php-parser/lib/PhpParser/']]));
        $locators[] = new PhpInternalSourceLocator($astPhp8Locator, $this->phpStormStubsSourceStubber);
        return new MemoizingSourceLocator(new AggregateSourceLocator($locators));
    }
}
