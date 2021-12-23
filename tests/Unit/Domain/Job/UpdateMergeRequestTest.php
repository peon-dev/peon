<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Job;

use PHPMate\Domain\Job\UpdateMergeRequest;
use PHPUnit\Framework\TestCase;

final class UpdateMergeRequestTest extends TestCase
{
    public function testLocalChangesNewMergeRequestShouldBeOpened(): void
    {
    }


    public function testLocalChangesMergeRequestAlreadyOpened(): void
    {
    }


    public function testNoChangesRemoteBranchExistsNewMergeRequestShouldBeOpened(): void
    {
    }


    public function testNoChangesRemoteBranchExistsMergeRequestAlreadyOpened(): void
    {
    }


    public function testNoLocalChangesNoRemoteBranchNothingHappens(): void
    {
    }
}
