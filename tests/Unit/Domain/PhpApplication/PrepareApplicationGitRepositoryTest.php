<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\PhpApplication;

use PHPMate\Domain\PhpApplication\PrepareApplicationGitRepository;
use PHPUnit\Framework\TestCase;

class PrepareApplicationGitRepositoryTest extends TestCase
{
    public function testLocalApplicationWillBePrepared(): void
    {
    }


    public function testRemoteBranchExistsRebaseSucceededWillBeForcePushed(): void
    {
    }


    public function testRemoteBranchExistsRebaseFailedBranchWillBeReset(): void
    {
    }


    public function testRemoteBranchExistsAndWillBeCheckedOut(): void
    {
    }


    public function testRemoteBranchNotExistsAndNewWillBeCheckedOut(): void
    {
    }
}
