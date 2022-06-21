<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Infrastructure\Project;

use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\Project\DoctrineGetProjectIdentifiers;
use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineGetProjectIdentifiersTest extends KernelTestCase
{
    private DoctrineGetProjectIdentifiers $doctrineGetProjectIdentifiers;


    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->doctrineGetProjectIdentifiers = $container->get(DoctrineGetProjectIdentifiers::class);
    }


    public function test(): void
    {
        $identifiers = $this->doctrineGetProjectIdentifiers->ownedByUser(
            new UserId(DataFixtures::USER_1_ID)
        );

        self::assertCount(2, $identifiers);

        self::assertSame(DataFixtures::USER_1_PROJECT_1_ID, $identifiers[0]->id);
        self::assertSame(DataFixtures::USER_1_PROJECT_2_ID, $identifiers[1]->id);
    }
}
