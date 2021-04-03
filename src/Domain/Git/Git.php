<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\FileSystem\WorkingDirectory;

final class Git
{
    private const USER_NAME = 'PHPMate';
    private const USER_EMAIL = 'bot@phpmate.io';

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
        $this->gitBinary->execInDirectory($workingDirectory, sprintf(
            'config user.name %s', self::USER_NAME
        ));

        $this->gitBinary->execInDirectory($workingDirectory, sprintf(
            'config user.email %s', self::USER_EMAIL
        ));

        $commitCommand = sprintf(
            'commit --author="%s <%s>" -a -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $this->gitBinary->execInDirectory($workingDirectory, $commitCommand);
        $this->gitBinary->execInDirectory($workingDirectory, 'push -u origin --all');
    }
}
