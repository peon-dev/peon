<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectRecipesControllerTest extends WebTestCase
{
    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::PROJECT_ID;

        $client->request('GET', "/project/$projectId/recipes");

        self::assertResponseIsSuccessful();

        // TODO: maybe add more assertions later
    }


    public function testFormCanBeSent(): void
    {
        $client = self::createClient();

        self::assertResponseIsSuccessful();
        // TODO
    }
}
