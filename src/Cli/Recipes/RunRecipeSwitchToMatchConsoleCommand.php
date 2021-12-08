<?php

declare(strict_types=1);

namespace PHPMate\Cli\Recipes;

use Nette\Utils\JsonException;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Tools\Composer\Composer;
use PHPMate\Domain\Tools\Rector\Exception\RectorCommandFailed;
use PHPMate\Domain\Tools\Rector\Rector;
use PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RunRecipeSwitchToMatchConsoleCommand extends Command
{
    public function __construct(
        private Rector $rector,
        private ProcessLogger $processLogger,
        private Composer $composer,
    ) {
        parent::__construct('phpmate:run-recipe:switch-to-match');
    }


    protected function configure(): void
    {
        $this->addArgument('application_path', InputArgument::REQUIRED, 'Path to PHP application');
    }


    /**
     * @throws RuntimeException
     * @throws JsonException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $applicationPath = $input->getArgument('application_path');
        assert(is_string($applicationPath));

        $output->writeln($applicationPath);

        $paths = $this->composer->getPsr4Roots($applicationPath);

        if ($paths === null) {
            throw new RuntimeException('PSR-4 roots must be defined to run this recipe!');
        }

        $configuration = new RectorProcessCommandConfiguration(
            autoloadFile: $applicationPath . '/vendor/autoload.php',
            config: __DIR__ . '/../../../vendor-bin/rector/config/switch-to-match.php',
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
