<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Git;

use Peon\Domain\Job\Job;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ExecuteCommand;
use Psr\Http\Message\UriInterface;

class Git
{
    private const USER_NAME = 'Peon';
    private const USER_EMAIL = 'peon@peon.dev';

    public function __construct(
        private ExecuteCommand $executeCommand,
    ) {}


    /**
     * @throws ProcessFailed
     */
    public function clone(Job $job, string $directory, UriInterface $remoteUri): void
    {
        $command = sprintf('clone %s .', (string) $remoteUri);

        $this->executeCommand->inDirectory($job, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function hasUncommittedChanges(Job $job, string $directory): bool
    {
        $output = $this->executeCommand->inDirectory($job, $directory, 'status --porcelain');

        return trim($output) !== '';
    }


    /**
     * @throws ProcessFailed
     */
    public function getCurrentBranch(Job $job, string $directory): string
    {
        $output = $this->executeCommand->inDirectory($job, $directory, 'rev-parse --abbrev-ref HEAD');

        return trim($output);
    }


    /**
     * @throws ProcessFailed
     */
    public function checkoutNewBranch(Job $job, string $directory, string $branch): void
    {
        $command = sprintf('checkout -b %s', $branch);

        $this->executeCommand->inDirectory($job, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function configureUser(Job $job, string $directory): void
    {
        $this->executeCommand->inDirectory($job, $directory, sprintf(
            'config user.name %s', self::USER_NAME
        ));

        $this->executeCommand->inDirectory($job, $directory, sprintf(
            'config user.email %s', self::USER_EMAIL
        ));
    }


    /**
     * @throws ProcessFailed
     */
    public function remoteBranchExists(Job $job, string $directory, string $branch): bool
    {
        $command = sprintf(
            'ls-remote --heads origin %s',
            $branch
        );

        $output = $this->executeCommand->inDirectory($job, $directory, $command);

        return trim($output) !== '';
    }


    /**
     * @throws ProcessFailed
     */
    public function rebaseBranchAgainstUpstream(Job $job, string $directory, string $mainBranch): void
    {
        $command = sprintf('rebase origin/%s', $mainBranch);

        $this->executeCommand->inDirectory($job, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function forcePushWithLease(Job $job, string $directory): void
    {
        $this->executeCommand->inDirectory($job, $directory, 'push -u origin --all --force-with-lease');
    }


    /**
     * @throws ProcessFailed
     */
    public function abortRebase(Job $job, string $directory): void
    {
        $this->executeCommand->inDirectory($job, $directory, 'rebase --abort');
    }


    /**
     * @throws ProcessFailed
     */
    public function resetCurrentBranch(Job $job, string $directory, string $mainBranch): void
    {
        $command = sprintf(
            'reset --hard %s',
            $mainBranch
        );

        $this->executeCommand->inDirectory($job, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function commit(Job $job, string $directory, string $commitMessage): void
    {
        $this->executeCommand->inDirectory($job, $directory, 'add .');

        $commitCommand = sprintf(
            'commit --author="%s <%s>" -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $this->executeCommand->inDirectory($job, $directory, $commitCommand);
    }


    /**
     * @return array<string>
     * @throws ProcessFailed
     */
    public function getChangedFilesSinceCommit(Job $job, string $directory, string $commitHash): array
    {
        $command = sprintf('diff --name-only --diff-filter=d %s origin/HEAD', $commitHash);

        $output = $this->executeCommand->inDirectory($job, $directory, $command);

        $files = preg_split("/\r\n|\n|\r/", trim($output), flags: PREG_SPLIT_NO_EMPTY);

        if (!is_array($files)) {
            throw new ProcessFailed();
        }

        return $files;
    }


    /**
     * @throws ProcessFailed
     */
    public function trackRemoteBranch(Job $job, string $directory, string $branch): void
    {
        $command = sprintf('branch --set-upstream-to origin/%s', $branch);

        $this->executeCommand->inDirectory($job, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function pull(Job $job, string $directory): void
    {
        $this->executeCommand->inDirectory($job, $directory, 'pull --rebase');
    }
}
