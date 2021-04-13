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

    }


    public function testGetCurrentBranch(): void
    {

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
}
