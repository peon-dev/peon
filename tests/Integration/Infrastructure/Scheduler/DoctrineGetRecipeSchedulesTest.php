<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Scheduler;

use Peon\Domain\Scheduler\GetRecipeSchedules;
use Peon\Infrastructure\Scheduler\DoctrineGetRecipeSchedules;
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
        $taskSchedules = $this->doctrineGetRecipeSchedules->get();

        self::assertCount(3, $taskSchedules);
    }
}
