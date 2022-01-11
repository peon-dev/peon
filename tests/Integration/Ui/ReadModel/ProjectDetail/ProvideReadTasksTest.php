<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\ProjectDetail;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadTasks;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProvideReadTasksTest extends KernelTestCase
{
    private ProvideReadTasks $provideReadTasksRecipes;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideReadTasksRecipes = $container->get(ProvideReadTasks::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $projectId = DataFixtures::PROJECT_1_ID;

        $readTasks = $this->provideReadTasksRecipes->provide(new ProjectId($projectId));

        self::assertCount(1, $readTasks);

        $task = $readTasks[0];
        self::assertSame(DataFixtures::TASK_ID, $task->taskId);
        self::assertNotNull($task->lastJobMergeRequestUrl);
    }
}
