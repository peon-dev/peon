<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;
use Psr\Http\Message\UriInterface;

final class Git
{
    private const USER_NAME = 'PHPMate';
    private const USER_EMAIL = 'bot@phpmate.io';

    public function __construct(
        private GitBinary $gitBinary
    ) {}


    public function clone(WorkingDirectory $workingDirectory, UriInterface $remoteUri): void
    {
        $command = sprintf('clone %s .', (string) $remoteUri);

        $this->gitBinary->execInWorkingDirectory($workingDirectory, $command);
    }


    public function hasUncommittedChanges(WorkingDirectory $workingDirectory): bool
    {
        $output = $this->gitBinary->execInWorkingDirectory($workingDirectory, 'status --porcelain');

        return $output !== '';
    }


    public function getCurrentBranch(WorkingDirectory $workingDirectory): string
    {
        return $this->gitBinary->execInWorkingDirectory($workingDirectory, 'rev-parse --abbrev-ref HEAD');
    }


    public function checkoutNewBranch(WorkingDirectory $workingDirectory, string $branch): void
    {
        $command = sprintf('checkout -b %s', $branch);

        $this->gitBinary->execInWorkingDirectory($workingDirectory, $command);
    }


    public function commitAndPushChanges(WorkingDirectory $workingDirectory, string $commitMessage): void
    {
        $this->gitBinary->execInWorkingDirectory($workingDirectory, sprintf(
            'config user.name %s', self::USER_NAME
        ));

        $this->gitBinary->execInWorkingDirectory($workingDirectory, sprintf(
            'config user.email %s', self::USER_EMAIL
        ));

        $commitCommand = sprintf(
            'commit --author="%s <%s>" -a -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $this->gitBinary->execInWorkingDirectory($workingDirectory, $commitCommand);
        $this->gitBinary->execInWorkingDirectory($workingDirectory, 'push -u origin --all');
    }
}
