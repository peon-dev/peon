<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Ui\ReadModel\Worker;

use Lcobucci\Clock\FrozenClock;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\Ui\ReadModel\Worker\ProvideActiveReadWorkers;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProvideActiveReadWorkersTest extends KernelTestCase
{
    private ProvideActiveReadWorkers $provideActiveReadWorkers;
    private FrozenClock $clock;

    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->provideActiveReadWorkers = $container->get(ProvideActiveReadWorkers::class);
        $this->clock = $container->get(FrozenClock::class);
    }


    public function testItWorks(): void
    {
        $this->clock->setTo(
            (new \DateTimeImmutable(DataFixtures::WORKER_LAST_SEEN_AT))->modify('+15 seconds')
        );

        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $readWorkers = $this->provideActiveReadWorkers->provide();

        self::assertCount(1, $readWorkers);
    }


    public function testNoRecentActiveWorkers(): void
    {
        $this->clock->setTo(
            (new \DateTimeImmutable(DataFixtures::WORKER_LAST_SEEN_AT))->modify('+2 hours')
        );

        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */
        $readWorkers = $this->provideActiveReadWorkers->provide();

        self::assertCount(0, $readWorkers);
    }
}
