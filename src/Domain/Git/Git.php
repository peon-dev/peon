<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use Psr\Http\Message\UriInterface;

final class Git
{
    private const USER_NAME = 'PHPMate';
    private const USER_EMAIL = 'bot@phpmate.io';

    public function __construct(
        private GitBinary $gitBinary
    ) {}


    public function clone(string $directory, UriInterface $remoteUri): void
    {
        $command = sprintf('clone %s .', (string) $remoteUri);

        $this->gitBinary->executeCommand($directory, $command);
    }


    public function hasUncommittedChanges(string $directory): bool
    {
        $output = $this->gitBinary->executeCommand($directory, 'status --porcelain');

        return $output !== '';
    }


    public function getCurrentBranch(string $directory): string
    {
        return $this->gitBinary->executeCommand($directory, 'rev-parse --abbrev-ref HEAD');
    }


    public function checkoutNewBranch(string $directory, string $branch): void
    {
        $command = sprintf('checkout -b %s', $branch);

        $this->gitBinary->executeCommand($directory, $command);
    }


    public function commitAndPushChanges(string $directory, string $commitMessage): void
    {
        $this->gitBinary->executeCommand($directory, sprintf(
            'config user.name %s', self::USER_NAME
        ));

        $this->gitBinary->executeCommand($directory, sprintf(
            'config user.email %s', self::USER_EMAIL
        ));

        $commitCommand = sprintf(
            'commit --author="%s <%s>" -a -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $this->gitBinary->executeCommand($directory, $commitCommand);
        $this->gitBinary->executeCommand($directory, 'push -u origin --all');
    }


    public function remoteBranchExists(string $directory, string $branchName): bool
    {
        $command = sprintf(
            'ls-remote --heads origin %s',
            $branchName
        );

        $output = $this->gitBinary->executeCommand($directory, $command);

        return $output !== '';
    }

    // forcePush() --force-with-lease
}
