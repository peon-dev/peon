<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Tools\Git;

use Generator;
use Nyholm\Psr7\Uri;
use Peon\Domain\Tools\Git\Git;
use Peon\Domain\Tools\Git\GitBinary;
use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Process\Value\ProcessResult;
use PHPUnit\Framework\TestCase;

class GitTest extends TestCase
{
    private ProcessLogger $logger;


    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new ProcessLogger();
    }


    public function testConfigureUser(): void
    {
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::exactly(2))
            ->method('executeCommand')
            ->withConsecutive(
                ['/', 'config user.name Peon'],
                ['/', 'config user.email peon@peon.dev'],
            )
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->configureUser('/');
    }


    public function testGetCurrentBranch(): void
    {
        $processResult = new ProcessResult('', 0, 'main', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'rev-parse --abbrev-ref HEAD')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $currentBranch = $git->getCurrentBranch('/');

        self::assertSame('main', $currentBranch);
    }


    /**
     * @dataProvider provideTestHasUncommittedChangesData
     */
    public function testHasUncommittedChanges(string $commandOutput, bool $expected): void
    {
        $processResult = new ProcessResult('', 0, $commandOutput, 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'status --porcelain')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $hasUncommittedChanges = $git->hasUncommittedChanges('/');

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
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'clone https://peon.dev .')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->clone('/', $remoteUri);
    }


    public function testCheckoutNewBranch(): void
    {
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'checkout -b peon')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->checkoutNewBranch('/', 'peon');
    }


    /**
     * @dataProvider provideTestRemoteBranchExistsData
     */
    public function testRemoteBranchExists(string $commandOutput, bool $expected): void
    {
        $processResult = new ProcessResult('', 0, $commandOutput, 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'ls-remote --heads origin peon')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $remoteBranchExists = $git->remoteBranchExists('/', 'peon');

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
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'branch --set-upstream-to origin/peon')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->trackRemoteBranch('/', 'peon');
    }


    public function testRebaseBranchAgainstUpstream(): void
    {
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'rebase origin/main')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->rebaseBranchAgainstUpstream('/', 'main');
    }


    public function testAbortRebase(): void
    {
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'rebase --abort')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->abortRebase('/');
    }


    public function testForcePushWithLease(): void
    {
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'push -u origin --all --force-with-lease')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->forcePushWithLease('/');
    }


    public function testResetCurrentBranch(): void
    {
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'reset --hard main')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->resetCurrentBranch('/', 'main');
    }


    public function testCommit(): void
    {
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::exactly(2))
            ->method('executeCommand')
            ->withConsecutive(
                ['/', 'add .'],
                ['/', 'commit --author="Peon <peon@peon.dev>" -m "Message"'],
            )
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->commit('/', 'Message');
    }


    public function testPull(): void
    {
        $processResult = new ProcessResult('', 0, '', 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'pull --rebase')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->pull('/');
    }


    /**
     * @dataProvider provideTestGetChangedFilesSinceCommitData
     * @param array<string> $expectedChangedFiles
     */
    public function testGetChangedFilesSinceCommit(string $commandOutput, array $expectedChangedFiles): void
    {
        $processResult = new ProcessResult('', 0, $commandOutput, 0);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                '/', 'diff --name-only --diff-filter=d hash origin/HEAD'
            )
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $changedFiles = $git->getChangedFilesSinceCommit('/', 'hash');

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

    // TODO: add tests covering situations - logger logs
}
