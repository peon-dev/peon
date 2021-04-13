<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Git;

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


    public function testHasUncommittedChanges(): void
    {

    }


    public function testClone(): void
    {

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
