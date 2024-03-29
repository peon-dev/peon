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

        // This is supposed to return results for all users
        self::assertCount(4, $taskSchedules);

        self::assertSame(DataFixtures::USER_1_PROJECT_1_ID, $taskSchedules[0]->projectId->id);
        self::assertEquals(RecipeName::TYPED_PROPERTIES, $taskSchedules[0]->recipeName);
        self::assertNull($taskSchedules[0]->lastTimeScheduledAt);

        self::assertSame(DataFixtures::USER_1_PROJECT_1_ID, $taskSchedules[1]->projectId->id);
        self::assertEquals(RecipeName::UNUSED_PRIVATE_METHODS, $taskSchedules[1]->recipeName);
        self::assertNotNull($taskSchedules[1]->lastTimeScheduledAt);
        self::assertSame(DataFixtures::JOB_4_DATETIME, $taskSchedules[1]->lastTimeScheduledAt->format('Y-m-d H:i:s'));

        self::assertSame(DataFixtures::USER_2_PROJECT_1_ID, $taskSchedules[2]->projectId->id);
        self::assertEquals(RecipeName::TYPED_PROPERTIES, $taskSchedules[2]->recipeName);
        self::assertNull($taskSchedules[2]->lastTimeScheduledAt);

        self::assertSame(DataFixtures::USER_2_PROJECT_1_ID, $taskSchedules[3]->projectId->id);
        self::assertEquals(RecipeName::UNUSED_PRIVATE_METHODS, $taskSchedules[3]->recipeName);
        self::assertNotNull($taskSchedules[3]->lastTimeScheduledAt);
        self::assertSame(DataFixtures::JOB_4_DATETIME, $taskSchedules[3]->lastTimeScheduledAt->format('Y-m-d H:i:s'));
    }
}
