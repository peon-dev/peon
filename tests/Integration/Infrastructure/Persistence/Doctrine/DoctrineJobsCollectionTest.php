<?php
declare(strict_types=1);

namespace PHPMate\Tests\Integration\Infrastructure\Persistence\Doctrine;

use PHPMate\Infrastructure\Persistence\Doctrine\DoctrineJobsCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineJobsCollectionTest extends KernelTestCase
{
    private DoctrineJobsCollection $doctrineJobsCollection;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->doctrineJobsCollection = $container->get(DoctrineJobsCollection::class);
    }


    public function testPersistence(): void
    {
        $this->doctrineJobsCollection->all();
    }
}
