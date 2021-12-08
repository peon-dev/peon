<?php

declare(strict_types=1);

namespace PHPMate\Cli\Recipes;

use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Tools\Rector\Rector;
use PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RunRecipeTypedPropertiesConsoleCommand extends Command
{
    public function __construct(
        private Rector $rector,
        private ProcessLogger $processLogger,
    ) {
        parent::__construct('phpmate:run-recipe:typed-properties');
    }


    protected function configure(): void
    {
        $this->addArgument('application_path', InputArgument::REQUIRED, 'Path to PHP application');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $applicationPath = $input->getArgument('application_path');
        assert(is_string($applicationPath));

        $output->writeln($applicationPath);

        // TODO: detect PSR-4 roots
        $paths = ['src'];

        $configuration = new RectorProcessCommandConfiguration(
            autoloadFile: $applicationPath . '/vendor/autoload.php',
            config: __DIR__ . '/../../../vendor-bin/rector/config/typed-properties.php',
            paths: $paths,
        );

        $this->rector->process($applicationPath, $configuration);

        // TODO: this could be improved, maybe?
        foreach ($this->processLogger->popLogs() as $log) {
            $output->writeln($log->output);
        }

        return self::SUCCESS;
    }
}
