<?php

declare(strict_types=1);

namespace PHPMate\Cli\Recipes;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RunRecipeSwitchToMatchConsoleCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct('phpmate:run-recipe:switch-to-match');
    }


    protected function configure(): void
    {
        $this->addArgument('application_path', InputArgument::REQUIRED, 'Path to PHP application');
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            $input->getArgument('application_path')
        );

        return self::SUCCESS;
    }
}
