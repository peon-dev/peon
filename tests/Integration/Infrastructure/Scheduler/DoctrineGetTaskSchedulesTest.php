<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Scheduler;

use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Infrastructure\Scheduler\DoctrineGetTaskSchedules;
use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineGetTaskSchedulesTest extends KernelTestCase
{
    private DoctrineGetTaskSchedules $doctrineGetTaskSchedules;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->doctrineGetTaskSchedules = $container->get(GetTaskSchedules::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $taskSchedules = $this->doctrineGetTaskSchedules->get();

        self::assertCount(1, $taskSchedules);

        self::assertSame(DataFixtures::TASK_ID, $taskSchedules[0]->taskId->id);
        self::assertSame(DataFixtures::TASK_SCHEDULE, $taskSchedules[0]->schedule->getExpression());
        self::assertNotNull($taskSchedules[0]->lastTimeScheduledAt);
        self::assertSame(DataFixtures::JOB_2_DATETIME, $taskSchedules[0]->lastTimeScheduledAt->format('Y-m-d H:i:s'));
    }
}
