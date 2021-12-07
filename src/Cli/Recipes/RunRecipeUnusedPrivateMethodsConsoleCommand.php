<?php

declare(strict_types=1);

namespace PHPMate\Cli\Recipes;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RunRecipeUnusedPrivateMethodsConsoleCommand extends Command
{
    public function __construct(
    ) {
        parent::__construct('phpmate:run-recipe:unused-private-methods');
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

        return self::SUCCESS;
    }
}
