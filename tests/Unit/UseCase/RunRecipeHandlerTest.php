<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryJobsCollection;
use PHPMate\UseCase\RunRecipe;
use PHPMate\UseCase\RunRecipeHandler;
use PHPUnit\Framework\TestCase;

final class RunRecipeHandlerTest extends TestCase
{
    public function testRunningRecipeWillAddPendingJob(): void
    {
        $jobsCollection = new InMemoryJobsCollection();
        $handler = new RunRecipeHandler();

        $handler->__invoke(
            new RunRecipe(
                RecipeName::TYPED_PROPERTIES()
            )
        );

        $jobs = $jobsCollection->all();

        // Make sure there are no jobs in collection
        self::assertCount(1, $jobs);

        $job = $jobs[array_key_first($jobs)];

        self::assertNotNull($job->scheduledAt);
        self::assertNull($job->startedAt);
    }
}
