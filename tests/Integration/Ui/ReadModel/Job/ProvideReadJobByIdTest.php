<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\Job;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\Value\JobId;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\Job\ProvideReadJobById;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProvideReadJobByIdTest extends KernelTestCase
{
    private ProvideReadJobById $provideReadJobById;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideReadJobById = $container->get(ProvideReadJobById::class);
    }


    public function testItWorks(): void
    {
        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $readJob = $this->provideReadJobById->provide(new JobId(DataFixtures::JOB_1_ID));

        self::assertFalse($readJob->isRecipe());
        self::assertNull($readJob->recipeName);
        self::assertNotNull($readJob->mergeRequestUrl);
        self::assertNotNull($readJob->taskId);
        self::assertNotNull($readJob->executionTime);
        self::assertTrue($readJob->hasSucceeded());
    }
}
