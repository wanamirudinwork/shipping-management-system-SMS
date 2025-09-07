<?php

declare (strict_types=1);
namespace PHPStan\Command;

use PHPStan\Diagnose\DiagnoseExtension;
use PHPStan\Diagnose\PHPStanDiagnoseExtension;
use PHPStan\ShouldNotHappenException;
use _PHPStan_14faee166\Symfony\Component\Console\Command\Command;
use _PHPStan_14faee166\Symfony\Component\Console\Input\InputInterface;
use _PHPStan_14faee166\Symfony\Component\Console\Input\InputOption;
use _PHPStan_14faee166\Symfony\Component\Console\Output\OutputInterface;
use function is_string;
final class DiagnoseCommand extends Command
{
    /**
     * @var string[]
     */
    private $composerAutoloaderProjectPaths;
    private const NAME = 'diagnose';
    /**
     * @param string[] $composerAutoloaderProjectPaths
     */
    public function __construct(array $composerAutoloaderProjectPaths)
    {
        $this->composerAutoloaderProjectPaths = $composerAutoloaderProjectPaths;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName(self::NAME)->setDescription('Shows diagnose information about PHPStan and extensions')->setDefinition([new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED, 'Path to project configuration file'), new InputOption(\PHPStan\Command\AnalyseCommand::OPTION_LEVEL, 'l', InputOption::VALUE_REQUIRED, 'Level of rule options - the higher the stricter'), new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'), new InputOption('debug', null, InputOption::VALUE_NONE, 'Show debug information - do not catch internal errors'), new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for clearing result cache')]);
    }
    protected function initialize(InputInterface $input, OutputInterface $output) : void
    {
        if ((bool) $input->getOption('debug')) {
            $application = $this->getApplication();
            if ($application === null) {
                throw new ShouldNotHappenException();
            }
            $application->setCatchExceptions(\false);
            return;
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $memoryLimit = $input->getOption('memory-limit');
        $autoloadFile = $input->getOption('autoload-file');
        $configuration = $input->getOption('configuration');
        $level = $input->getOption(\PHPStan\Command\AnalyseCommand::OPTION_LEVEL);
        if (!is_string($memoryLimit) && $memoryLimit !== null || !is_string($autoloadFile) && $autoloadFile !== null || !is_string($configuration) && $configuration !== null || !is_string($level) && $level !== null) {
            throw new ShouldNotHappenException();
        }
        try {
            $inceptionResult = \PHPStan\Command\CommandHelper::begin($input, $output, [], $memoryLimit, $autoloadFile, $this->composerAutoloaderProjectPaths, $configuration, null, $level, \false);
        } catch (\PHPStan\Command\InceptionNotSuccessfulException $e) {
            return 1;
        }
        $container = $inceptionResult->getContainer();
        $output = $inceptionResult->getStdOutput();
        /** @var PHPStanDiagnoseExtension $phpstanDiagnoseExtension */
        $phpstanDiagnoseExtension = $container->getService('phpstanDiagnoseExtension');
        // not using tag for this extension to make sure it's always first
        $phpstanDiagnoseExtension->print($output);
        /** @var DiagnoseExtension $extension */
        foreach ($container->getServicesByTag(DiagnoseExtension::EXTENSION_TAG) as $extension) {
            $extension->print($output);
        }
        return 0;
    }
}
