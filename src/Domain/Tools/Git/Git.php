<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Git;

use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Tools\Git\Exception\GitCommandFailed;
use Peon\Infrastructure\Process\Symfony\SymfonyProcessToProcessResultMapper;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Process\Process;

class Git
{
    private const USER_NAME = 'Peon';
    private const USER_EMAIL = 'peon@peon.dev';

    public function __construct(
        private GitBinary $gitBinary,
        private ProcessLogger $processLogger
    ) {}


    /**
     * @throws GitCommandFailed
     */
    public function clone(string $directory, UriInterface $remoteUri): void
    {
        $command = sprintf('clone %s .', (string) $remoteUri);

        $result = $this->gitBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);
    }


    /**
     * @throws GitCommandFailed
     */
    public function hasUncommittedChanges(string $directory): bool
    {
        $result = $this->gitBinary->executeCommand($directory, 'status --porcelain');

        $this->processLogger->logResult($result);

        return trim($result->output) !== '';
    }


    /**
     * @throws GitCommandFailed
     */
    public function getCurrentBranch(string $directory): string
    {
        $result = $this->gitBinary->executeCommand($directory, 'rev-parse --abbrev-ref HEAD');

        $this->processLogger->logResult($result);

        return trim($result->output);
    }


    /**
     * @throws GitCommandFailed
     */
    public function checkoutNewBranch(string $directory, string $branch): void
    {
        $command = sprintf('checkout -b %s', $branch);

        $result = $this->gitBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);
    }


    /**
     * @throws GitCommandFailed
     */
    public function configureUser(string $directory): void
    {
        $result = $this->gitBinary->executeCommand($directory, sprintf(
            'config user.name %s', self::USER_NAME
        ));

        $this->processLogger->logResult($result);

        $result = $this->gitBinary->executeCommand($directory, sprintf(
            'config user.email %s', self::USER_EMAIL
        ));

        $this->processLogger->logResult($result);
    }


    /**
     * @throws GitCommandFailed
     */
    public function remoteBranchExists(string $directory, string $branch): bool
    {
        $command = sprintf(
            'ls-remote --heads origin %s',
            $branch
        );

        $result = $this->gitBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);

        return trim($result->output) !== '';
    }


    /**
     * @throws GitCommandFailed
     */
    public function rebaseBranchAgainstUpstream(string $directory, string $mainBranch): void
    {
        $command = sprintf('rebase origin/%s', $mainBranch);
        $result = $this->gitBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);
    }


    /**
     * @throws GitCommandFailed
     */
    public function forcePushWithLease(string $directory): void
    {
        $result = $this->gitBinary->executeCommand($directory, 'push -u origin --all --force-with-lease');

        $this->processLogger->logResult($result);
    }


    /**
     * @throws GitCommandFailed
     */
    public function abortRebase(string $directory): void
    {
        $result = $this->gitBinary->executeCommand($directory, 'rebase --abort');

        $this->processLogger->logResult($result);
    }


    /**
     * @throws GitCommandFailed
     */
    public function resetCurrentBranch(string $directory, string $mainBranch): void
    {
        $command = sprintf(
            'reset --hard %s',
            $mainBranch
        );

        $result = $this->gitBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);
    }


    /**
     * @throws GitCommandFailed
     */
    public function commit(string $directory, string $commitMessage): void
    {
        $result = $this->gitBinary->executeCommand($directory, 'add .');
        $this->processLogger->logResult($result);

        $commitCommand = sprintf(
            'commit --author="%s <%s>" -m "%s"',
            self::USER_NAME,
            self::USER_EMAIL,
            $commitMessage,
        );

        $result = $this->gitBinary->executeCommand($directory, $commitCommand);

        $this->processLogger->logResult($result);
    }


    /**
     * @return array<string>
     * @throws GitCommandFailed
     */
    public function getChangedFilesSinceCommit(string $directory, string $commitHash): array
    {
        $command = sprintf('diff --name-only --diff-filter=d %s origin/HEAD', $commitHash);

        $result = $this->gitBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);

        $files = preg_split("/\r\n|\n|\r/", trim($result->output), flags: PREG_SPLIT_NO_EMPTY);

        if (!is_array($files)) {
            throw new GitCommandFailed();
        }

        return $files;
    }


    /**
     * @throws GitCommandFailed
     */
    public function trackRemoteBranch(string $directory, string $branch): void
    {
        $command = sprintf('branch --set-upstream-to origin/%s', $branch);

        $result = $this->gitBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);
    }


    /**
     * @throws GitCommandFailed
     */
    public function pull(string $directory): void
    {
        $result = $this->gitBinary->executeCommand($directory, 'pull --rebase');

        $this->processLogger->logResult($result);
    }
}
