<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Git;

use Nyholm\Psr7\Uri;
use PHPMate\Domain\Git\Git;
use PHPMate\Domain\Git\GitBinary;
use PHPMate\Domain\Git\RebaseFailed;
use PHPUnit\Framework\TestCase;

class GitTest extends TestCase
{
    public function testConfigureUser(): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::exactly(2))
            ->method('executeCommand')
            ->withConsecutive(
                ['/', 'config user.name PHPMate'],
                ['/', 'config user.email bot@phpmate.io'],
            );

        $git = new Git($gitBinary);
        $git->configureUser('/');
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
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'checkout origin/phpmate');

        $git = new Git($gitBinary);
        $git->checkoutRemoteBranch('/', 'phpmate');
    }


    public function testRebaseBranchAgainstUpstream(): void
    {
        $output = <<<OUTPUT
First, rewinding head to replay your work on top of it...
Fast-forwarded master to origin/master.
OUTPUT;

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'rebase origin/main')
            ->willReturn($output);

        $git = new Git($gitBinary);
        $git->rebaseBranchAgainstUpstream('/', 'main');
    }


    public function testRebaseBranchAgainstUpstreamWillFail(): void
    {
        $this->expectException(RebaseFailed::class);

        $output = <<<OUTPUT
First, rewinding head to replay your work on top of it...
Applying: c
Using index info to reconstruct a base tree...
M       src/Domain/Git/Git.php
Falling back to patching base and 3-way merge...
Auto-merging src/Domain/Git/Git.php
CONFLICT (content): Merge conflict in src/Domain/Git/Git.php
error: Failed to merge in the changes.
Patch failed at 0001 c
hint: Use 'git am --show-current-patch' to see the failed patch
Resolve all conflicts manually, mark them as resolved with
"git add/rm <conflicted_files>", then run "git rebase --continue".
You can instead skip this commit: run "git rebase --skip".
To abort and get back to the state before "git rebase", run "git rebase --abort".
OUTPUT;

        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'rebase origin/main')
            ->willReturn($output);

        $git = new Git($gitBinary);
        $git->rebaseBranchAgainstUpstream('/', 'main');
    }


    public function testForcePush(): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'push -u origin --all --force-with-lease');

        $git = new Git($gitBinary);
        $git->forcePush('/');
    }


    public function testResetBranch(): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'branch --force phpmate main');

        $git = new Git($gitBinary);
        $git->resetBranch('/', 'phpmate', 'main');
    }


    public function testCommit(): void
    {
        $gitBinary = $this->createMock(GitBinary::class);
        $gitBinary->expects(self::once())
            ->method('executeCommand')
            ->with('/', 'commit --author="PHPMate <bot@phpmate.io>" -a -m "Message"');

        $git = new Git($gitBinary);
        $git->commit('/', 'Message');
    }
}
