<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Git;

use Nyholm\Psr7\Uri;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Git\GitBinary;
use PHPMate\Domain\Git\RebaseFailed;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Process\ProcessResult;
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
        $processResult = new ProcessResult('', 0, '');

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::exactly(2))
            ->method('executeCommand')
            ->withConsecutive(
                ['/', 'config user.name PHPMate'],
                ['/', 'config user.email bot@phpmate.io'],
            )
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->configureUser('/');
    }


    public function testGetCurrentBranch(): void
    {
        $processResult = new ProcessResult('', 0, 'main');

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
        $processResult = new ProcessResult('', 0, $commandOutput);

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
     * @return \Generator<array{string, bool}>
     */
    public function provideTestHasUncommittedChangesData(): \Generator
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
        $remoteUri = new Uri('https://phpmate.io');
        $processResult = new ProcessResult('', 0, '');

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'clone https://phpmate.io .')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->clone('/', $remoteUri);
    }


    public function testCheckoutNewBranch(): void
    {
        $processResult = new ProcessResult('', 0, '');

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'checkout -b phpmate')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->checkoutNewBranch('/', 'phpmate');
    }


    /**
     * @dataProvider provideTestRemoteBranchExistsData
     */
    public function testRemoteBranchExists(string $commandOutput, bool $expected): void
    {
        $processResult = new ProcessResult('', 0, $commandOutput);

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'ls-remote --heads origin phpmate')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $remoteBranchExists = $git->remoteBranchExists('/', 'phpmate');

        self::assertSame($expected, $remoteBranchExists);
    }


    /**
     * @return \Generator<array{string, bool}>
     */
    public function provideTestRemoteBranchExistsData(): \Generator
    {
        yield [
            'a076d105a41bd46485eed50a5b5ffe2e20f43a4e	refs/heads/phpmate',
            true,
        ];

        yield [
            '',
            false,
        ];
    }


    public function testCheckoutRemoteBranch(): void
    {
        $processResult = new ProcessResult('', 0, '');

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'checkout origin/phpmate')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->checkoutRemoteBranch('/', 'phpmate');
    }


    public function testRebaseBranchAgainstUpstream(): void
    {
        $processResult = new ProcessResult('', 0, '');

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'rebase origin/main')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->rebaseBranchAgainstUpstream('/', 'main');
    }


    public function testRebaseBranchAgainstUpstreamWillFail(): void
    {
        $this->expectException(RebaseFailed::class);

        $processResult = new ProcessResult('', 2, '');

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
        $processResult = new ProcessResult('', 0, '');

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'rebase --abort')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->abortRebase('/');
    }


    public function testForcePush(): void
    {
        $processResult = new ProcessResult('', 0, '');

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'push -u origin --all --force-with-lease')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->forcePush('/');
    }


    public function testResetCurrentBranch(): void
    {
        $processResult = new ProcessResult('', 0, '');

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
        $processResult = new ProcessResult('', 0, '');

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'commit --author="PHPMate <bot@phpmate.io>" -a -m "Message"')
            ->willReturn($processResult);

        $git = new Git($gitBinary, $this->logger);
        $git->commit('/', 'Message');
    }

    // TODO: add tests covering situations - logger logs
}