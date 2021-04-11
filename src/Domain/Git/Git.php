<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;
use Psr\Http\Message\UriInterface;

// TODO: cover with unit tests
final class Git
{
    private const USER_NAME = 'PHPMate';
    private const USER_EMAIL = 'bot@phpmate.io';

    public function __construct(
        private GitBinary $gitBinary
    ) {}


    public function clone(WorkingDirectory $projectDirectory, UriInterface $remoteUri): void
    {
        $command = sprintf('clone %s .', (string) $remoteUri);

        $this->gitBinary->executeCommand($projectDirectory, $command);
    }


    public function hasUncommittedChanges(WorkingDirectory $projectDirectory): bool
    {
        $output = $this->gitBinary->executeCommand($projectDirectory, 'status --porcelain');

        return $output !== '';
    }


    public function getCurrentBranch(WorkingDirectory $projectDirectory): string
    {
        return $this->gitBinary->executeCommand($projectDirectory, 'rev-parse --abbrev-ref HEAD');
    }


    public function checkoutNewBranch(WorkingDirectory $projectDirectory, string $branch): void
    {
        $command = sprintf('checkout -b %s', $branch);

        $this->gitBinary->executeCommand($projectDirectory, $command);
    }


    public function commitAndPushChanges(WorkingDirectory $projectDirectory, string $commitMessage): void
    {
        $this->gitBinary->executeCommand($projectDirectory, sprintf(
            'config user.name %s', self::USER_NAME
        ));

        $this->gitBinary->executeCommand($projectDirectory, sprintf(
            'config user.email %s', self::USER_EMAIL
        ));

        $commitCommand = sprintf(
            'commit --author="%s <%s>" -a -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $this->gitBinary->executeCommand($projectDirectory, $commitCommand);
        $this->gitBinary->executeCommand($projectDirectory, 'push -u origin --all');
    }
}
