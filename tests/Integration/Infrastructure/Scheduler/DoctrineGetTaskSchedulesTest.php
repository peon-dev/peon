<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Scheduler;

use Peon\Domain\Scheduler\GetTaskSchedules;
use Peon\Infrastructure\Scheduler\DoctrineGetTaskSchedules;
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

        self::assertCount(3, $taskSchedules);
    }
}
