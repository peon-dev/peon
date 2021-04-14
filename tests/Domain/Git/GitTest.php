<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Git;

use Nyholm\Psr7\Uri;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Git\GitBinary;
use PHPUnit\Framework\TestCase;

class GitTest extends TestCase
{
    public function testCommitAndPushChanges(): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::exactly(4))
            ->method('executeCommand')
            ->withConsecutive(
                ['/', 'config user.name PHPMate'],
                ['/', 'config user.email bot@phpmate.io'],
                ['/', 'commit --author="PHPMate <bot@phpmate.io>" -a -m "Message"'],
                ['/', 'push -u origin --all'],
            );

        $git = new Git($gitBinary);
        $git->commitAndPushChanges('/', 'Message');
    }


    public function testGetCurrentBranch(): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'rev-parse --abbrev-ref HEAD')
            ->willReturn('main');

        $git = new Git($gitBinary);
        $currentBranch = $git->getCurrentBranch('/');

        self::assertSame('main', $currentBranch);
    }


    /**
     * @dataProvider provideTestHasUncommittedChangesData
     */
    public function testHasUncommittedChanges(string $commandOutput, bool $expected): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'status --porcelain')
            ->willReturn($commandOutput);

        $git = new Git($gitBinary);
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

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'clone https://phpmate.io .');

        $git = new Git($gitBinary);
        $git->clone('/', $remoteUri);
    }


    public function testCheckoutNewBranch(): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'checkout -b phpmate');

        $git = new Git($gitBinary);
        $git->checkoutNewBranch('/', 'phpmate');
    }


    /**
     * @dataProvider provideTestRemoteBranchExistsData
     */
    public function testRemoteBranchExists(string $commandOutput, bool $expected): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'ls-remote --heads origin phpmate')
            ->willReturn($commandOutput);

        $git = new Git($gitBinary);
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
    }


    public function testRebaseBranchAgainstUpstream(): void
    {
    }


    public function testForcePush(): void
    {
    }


    public function testResetBranch(): void
    {
    }


    public function testCommit(): void
    {
    }
}
