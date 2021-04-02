<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;

final class Git
{
    private const USER_NAME = 'PHPMate';
    private const USER_EMAIL = 'j.mikes@me.com';

    public function __construct(
        private GitBinary $gitBinary
    ) {}


    public function clone(WorkingDirectory $workingDirectory, string $remoteUri): void
    {
        $command = sprintf('clone %s .', $remoteUri);

        $this->gitBinary->execInDirectory($workingDirectory, $command);
    }


    public function hasUncommittedChanges(WorkingDirectory $workingDirectory): bool
    {
        $output = $this->gitBinary->execInDirectory($workingDirectory, 'status --porcelain');

        return $output !== '';
    }


    public function checkoutNewBranch(WorkingDirectory $workingDirectory, string $branch): void
    {
        $command = sprintf('checkout -b %s', $branch);

        $this->gitBinary->execInDirectory($workingDirectory, $command);
    }


    public function commitAndPushChanges(WorkingDirectory $workingDirectory, string $commitMessage): void
    {
        $commitCommand = sprintf(
            '-c "user.name=%s" -c "user.email=%s" commit -a -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $this->gitBinary->execInDirectory($workingDirectory, $commitCommand);
        $this->gitBinary->execInDirectory($workingDirectory, 'push -u origin --all');
    }
}
