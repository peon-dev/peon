<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Tools\Git;

use Generator;
use Nyholm\Psr7\Uri;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Tools\Git\Git;
use PHPUnit\Framework\TestCase;

class GitTest extends TestCase
{
    private JobId $jobId;


    protected function setUp(): void
    {
        parent::setUp();

        $this->jobId = new JobId('');
    }


    public function testConfigureUser(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::exactly(2))
            ->method('inDirectory')
            ->withConsecutive(
                [$this->jobId, '/', 'git config user.name Peon'],
                [$this->jobId, '/', 'git config user.email peon@peon.dev'],
            );

        $git = new Git($executeCommand);
        $git->configureUser($this->jobId, '/');
    }


    public function testGetCurrentBranch(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git rev-parse --abbrev-ref HEAD')
            ->willReturn('main');

        $git = new Git($executeCommand);
        $currentBranch = $git->getCurrentBranch($this->jobId, '/');

        self::assertSame('main', $currentBranch);
    }


    /**
     * @dataProvider provideTestHasUncommittedChangesData
     */
    public function testHasUncommittedChanges(string $processOutput, bool $expected): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git status --porcelain')
            ->willReturn($processOutput);

        $git = new Git($executeCommand);
        $hasUncommittedChanges = $git->hasUncommittedChanges($this->jobId, '/');

        self::assertSame($expected, $hasUncommittedChanges);
    }


    /**
     * @return Generator<array{string, bool}>
     */
    public function provideTestHasUncommittedChangesData(): Generator
    {
        yield [
            ' M some/file.php',
            true,
        ];

        yield [
            '',
            false,
        ];
    }


    public function testClone(): void
    {
        $remoteUri = new Uri('https://peon.dev');

        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git clone https://peon.dev .');

        $git = new Git($executeCommand);
        $git->clone($this->jobId, '/', $remoteUri);
    }


    public function testSwitchToBranch(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git switch --force-create peon');

        $git = new Git($executeCommand);
        $git->switchToBranch($this->jobId, '/', 'peon');
    }


    /**
     * @dataProvider provideTestRemoteBranchExistsData
     */
    public function testRemoteBranchExists(string $processOutput, bool $expected): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git ls-remote --heads origin peon')
            ->willReturn($processOutput);

        $git = new Git($executeCommand);
        $remoteBranchExists = $git->remoteBranchExists($this->jobId, '/', 'peon');

        self::assertSame($expected, $remoteBranchExists);
    }


    /**
     * @return Generator<array{string, bool}>
     */
    public function provideTestRemoteBranchExistsData(): Generator
    {
        yield [
            'a076d105a41bd46485eed50a5b5ffe2e20f43a4e	refs/heads/peon',
            true,
        ];

        yield [
            '',
            false,
        ];
    }


    public function testTrackRemoteBranch(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git branch --set-upstream-to origin/peon');

        $git = new Git($executeCommand);
        $git->trackRemoteBranch($this->jobId, '/', 'peon');
    }


    public function testRebaseBranchAgainstUpstream(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git rebase origin/main');

        $git = new Git($executeCommand);
        $git->rebaseBranchAgainstUpstream($this->jobId, '/', 'main');
    }


    public function testAbortRebase(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git rebase --abort');

        $git = new Git($executeCommand);
        $git->abortRebase($this->jobId, '/');
    }


    public function testForcePushWithLease(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git push -u origin --force-with-lease');

        $git = new Git($executeCommand);
        $git->forcePushWithLease($this->jobId, '/');
    }


    public function testResetCurrentBranch(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git reset --hard main');

        $git = new Git($executeCommand);
        $git->resetCurrentBranch($this->jobId, '/', 'main');
    }


    public function testCommit(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::exactly(2))
            ->method('inDirectory')
            ->withConsecutive(
                [$this->jobId, '/', 'git add .'],
                [$this->jobId, '/', 'git commit --author="Peon <peon@peon.dev>" -m "Message"'],
            );

        $git = new Git($executeCommand);
        $git->commit($this->jobId, '/', 'Message');
    }


    public function testPull(): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with($this->jobId, '/', 'git pull --rebase');

        $git = new Git($executeCommand);
        $git->pull($this->jobId, '/');
    }


    /**
     * @dataProvider provideTestGetChangedFilesSinceCommitData
     * @param array<string> $expectedChangedFiles
     */
    public function testGetChangedFilesSinceCommit(string $processOutput, array $expectedChangedFiles): void
    {
        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with(
                $this->jobId, '/', 'git diff --name-only --diff-filter=d hash origin/HEAD'
            )
            ->willReturn($processOutput);

        $git = new Git($executeCommand);
        $changedFiles = $git->getChangedFilesSinceCommit($this->jobId, '/', 'hash');

        self::assertSame($expectedChangedFiles, $changedFiles);
    }


    /**
     * @return Generator<array{string, array<string>}>
     */
    public function provideTestGetChangedFilesSinceCommitData(): Generator
    {
        yield [
            '',
            [],
        ];

        yield [
            'file.txt',
            ['file.txt'],
        ];

        yield [
            'file1.txt
file2.txt',
            ['file1.txt', 'file2.txt']
        ];
    }
}
