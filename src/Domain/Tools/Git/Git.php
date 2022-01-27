<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Git;

use Peon\Domain\Job\Value\JobId;
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
    public function clone(JobId $jobId, string $directory, UriInterface $remoteUri): void
    {
        $command = sprintf('clone %s .', (string) $remoteUri);

        $this->executeCommand->inDirectory($jobId, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function hasUncommittedChanges(JobId $jobId, string $directory): bool
    {
        $output = $this->executeCommand->inDirectory($jobId, $directory, 'status --porcelain');

        return trim($output->toString()) !== '';
    }


    /**
     * @throws ProcessFailed
     */
    public function getCurrentBranch(JobId $jobId, string $directory): string
    {
        $output = $this->executeCommand->inDirectory($jobId, $directory, 'rev-parse --abbrev-ref HEAD');

        return trim($output->toString());
    }


    /**
     * @throws ProcessFailed
     */
    public function checkoutNewBranch(JobId $jobId, string $directory, string $branch): void
    {
        $command = sprintf('checkout -b %s', $branch);

        $this->executeCommand->inDirectory($jobId, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function configureUser(JobId $jobId, string $directory): void
    {
        $this->executeCommand->inDirectory($jobId, $directory, sprintf(
            'config user.name %s', self::USER_NAME
        ));

        $this->executeCommand->inDirectory($jobId, $directory, sprintf(
            'config user.email %s', self::USER_EMAIL
        ));
    }


    /**
     * @throws ProcessFailed
     */
    public function remoteBranchExists(JobId $jobId, string $directory, string $branch): bool
    {
        $command = sprintf(
            'ls-remote --heads origin %s',
            $branch
        );

        $output = $this->executeCommand->inDirectory($jobId, $directory, $command);

        return trim($output->toString()) !== '';
    }


    /**
     * @throws ProcessFailed
     */
    public function rebaseBranchAgainstUpstream(JobId $jobId, string $directory, string $mainBranch): void
    {
        $command = sprintf('rebase origin/%s', $mainBranch);

        $this->executeCommand->inDirectory($jobId, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function forcePushWithLease(JobId $jobId, string $directory): void
    {
        $this->executeCommand->inDirectory($jobId, $directory, 'push -u origin --all --force-with-lease');
    }


    /**
     * @throws ProcessFailed
     */
    public function abortRebase(JobId $jobId, string $directory): void
    {
        $this->executeCommand->inDirectory($jobId, $directory, 'rebase --abort');
    }


    /**
     * @throws ProcessFailed
     */
    public function resetCurrentBranch(JobId $jobId, string $directory, string $mainBranch): void
    {
        $command = sprintf(
            'reset --hard %s',
            $mainBranch
        );

        $this->executeCommand->inDirectory($jobId, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function commit(JobId $jobId, string $directory, string $commitMessage): void
    {
        $this->executeCommand->inDirectory($jobId, $directory, 'add .');

        $commitCommand = sprintf(
            'commit --author="%s <%s>" -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $this->executeCommand->inDirectory($jobId, $directory, $commitCommand);
    }


    /**
     * @return array<string>
     * @throws ProcessFailed
     */
    public function getChangedFilesSinceCommit(JobId $jobId, string $directory, string $commitHash): array
    {
        $command = sprintf('diff --name-only --diff-filter=d %s origin/HEAD', $commitHash);

        $output = $this->executeCommand->inDirectory($jobId, $directory, $command);

        $files = preg_split("/\r\n|\n|\r/", trim($output->toString()), flags: PREG_SPLIT_NO_EMPTY);

        if (!is_array($files)) {
            throw new ProcessFailed();
        }

        return $files;
    }


    /**
     * @throws ProcessFailed
     */
    public function trackRemoteBranch(JobId $jobId, string $directory, string $branch): void
    {
        $command = sprintf('branch --set-upstream-to origin/%s', $branch);

        $this->executeCommand->inDirectory($jobId, $directory, $command);
    }


    /**
     * @throws ProcessFailed
     */
    public function pull(JobId $jobId, string $directory): void
    {
        $this->executeCommand->inDirectory($jobId, $directory, 'pull --rebase');
    }
}
