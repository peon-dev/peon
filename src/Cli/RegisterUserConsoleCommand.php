<?php

declare(strict_types=1);

namespace Peon\Cli;

use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RegisterUser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RegisterUserConsoleCommand extends Command
{
    const ARGUMENT_USERNAME = 'username';
    const ARGUMENT_PASSWORD = 'plainTextPassword';

    public function __construct(
        private CommandBus $commandBus,
    ) {
        parent::__construct('peon:user:register');
    }


    protected function configure(): void
    {
        $this->addArgument(self::ARGUMENT_USERNAME, InputArgument::REQUIRED);

        $this->addArgument(self::ARGUMENT_PASSWORD, InputArgument::REQUIRED);
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument(self::ARGUMENT_USERNAME);
        assert(is_string($username));

        $plainTextPassword = $input->getArgument(self::ARGUMENT_PASSWORD);
        assert(is_string($plainTextPassword));

        $this->commandBus->dispatch(
            new RegisterUser($username, $plainTextPassword),
        );

        $output->writeln('<info>User created</info>');

        return self::SUCCESS;
    }
}
