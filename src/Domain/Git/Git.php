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


    public function configureUser(string $directory): void
    {
        $this->gitBinary->executeCommand($directory, sprintf(
            'config user.name %s', self::USER_NAME
        ));

        $this->gitBinary->executeCommand($directory, sprintf(
            'config user.email %s', self::USER_EMAIL
        ));
    }


    public function remoteBranchExists(string $directory, string $branch): bool
    {
        $command = sprintf(
            'ls-remote --heads origin %s',
            $branch
        );

        $output = $this->gitBinary->executeCommand($directory, $command);

        return $output !== '';
    }


    public function checkoutRemoteBranch(string $directory, string $branch): void
    {
        $command = sprintf('checkout origin/%s', $branch);

        $this->gitBinary->executeCommand($directory, $command);
    }


    /**
     * @throws RebaseFailed
     */
    public function rebaseBranchAgainstUpstream(string $directory, string $mainBranch): void
    {
        $command = sprintf('rebase origin/%s', $mainBranch);
        $output = $this->gitBinary->executeCommand($directory, $command);

        // TODO: detect by != 0 exit code
        if (str_contains($output, 'error: Failed to merge in the changes')) {
            throw new RebaseFailed();
        }
    }


    public function forcePush(string $directory): void
    {
        $this->gitBinary->executeCommand($directory, 'push -u origin --all --force-with-lease');
    }


    public function resetBranch(string $directory, string $branchToReset, string $mainBranch): void
    {
        $command = sprintf(
            'branch --force %s %s',
            $branchToReset,
            $mainBranch
        );

        $this->gitBinary->executeCommand($directory, $command);
    }


    public function commit(string $directory, string $commitMessage): void
    {
        $commitCommand = sprintf(
            'commit --author="%s <%s>" -a -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $this->gitBinary->executeCommand($directory, $commitCommand);
    }
}
