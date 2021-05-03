<?php

declare(strict_types=1);

namespace PHPMate\Domain\Git;

use PHPMate\Domain\Logger\Logger;
use Psr\Http\Message\UriInterface;

final class Git
{
    private const USER_NAME = 'PHPMate';
    private const USER_EMAIL = 'bot@phpmate.io';

    public function __construct(
        private GitBinary $gitBinary,
        private Logger $logger
    ) {}


    /**
     * @throws GitCommandFailed
     */
    public function clone(string $directory, UriInterface $remoteUri): void
    {
        $command = sprintf('clone %s .', (string) $remoteUri);

        $result = $this->gitBinary->executeCommand($directory, $command);

        $this->logger->log($command, $result->getOutput());
    }


    public function hasUncommittedChanges(string $directory): bool
    {
        $result = $this->gitBinary->executeCommand($directory, 'status --porcelain');

        return trim($result->getOutput()) !== '';
    }


    public function getCurrentBranch(string $directory): string
    {
        $result = $this->gitBinary->executeCommand($directory, 'rev-parse --abbrev-ref HEAD');

        return trim($result->getOutput());
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

        $result = $this->gitBinary->executeCommand($directory, $command);

        return trim($result->getOutput()) !== '';
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
        $result = $this->gitBinary->executeCommand($directory, $command);

        if ($result->getExitCode() !== 0) {
            throw new RebaseFailed($result->getOutput());
        }
    }


    public function forcePush(string $directory): void
    {
        $this->gitBinary->executeCommand($directory, 'push -u origin --all --force-with-lease');
    }


    public function abortRebase(string $directory): void
    {
        $this->gitBinary->executeCommand($directory, 'rebase --abort');
    }


    public function resetCurrentBranch(string $directory, string $mainBranch): void
    {
        $command = sprintf(
            'reset --hard %s',
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
