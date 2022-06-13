<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Scheduler;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Scheduler\GetRecipeSchedules;
use Peon\Infrastructure\Scheduler\DoctrineGetRecipeSchedules;
use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineGetRecipeSchedulesTest extends KernelTestCase
{
    private DoctrineGetRecipeSchedules $doctrineGetRecipeSchedules;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->doctrineGetRecipeSchedules = $container->get(GetRecipeSchedules::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $taskSchedules = $this->doctrineGetRecipeSchedules->all();

        self::assertCount(2, $taskSchedules);

        self::assertSame(DataFixtures::USER_1_PROJECT_1_ID, $taskSchedules[0]->projectId->id);
        self::assertSame(RecipeName::UNUSED_PRIVATE_METHODS, $taskSchedules[0]->recipeName);
        self::assertNotNull($taskSchedules[0]->lastTimeScheduledAt);
        self::assertSame(DataFixtures::JOB_4_DATETIME, $taskSchedules[0]->lastTimeScheduledAt->format('Y-m-d H:i:s'));

        self::assertSame(DataFixtures::USER_1_PROJECT_1_ID, $taskSchedules[1]->projectId->id);
        self::assertSame(RecipeName::TYPED_PROPERTIES, $taskSchedules[1]->recipeName);
        self::assertNull($taskSchedules[1]->lastTimeScheduledAt);
    }
}
