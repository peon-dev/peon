<?php

declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPUnit\Framework\TestCase;

final class ExecuteRecipeJobHandlerTest extends TestCase
{
    public function testNotFoundJobWillThrowException(): void
    {
    }


    public function testMissingProjectWillCancelJob(): void
    {
    }


    public function testFailedCloningRepositoryWillFailJob(): void
    {
    }


    public function testFailedBuildingApplicationWillFailJob(): void
    {
    }


    public function testFailedRunningRecipeWillFailJob(): void
    {
    }


    public function testFailedOpeningMergeRequestWillFailJob(): void
    {
    }


    public function testJobWillSucceed(): void
    {
    }
}
