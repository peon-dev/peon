<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Infrastructure\Cookbook\StaticRecipesCollection;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CookbookControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomProjectId = Uuid::uuid4()->toString();

        $client->request('GET', "/projects/$randomProjectId/cookbook");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $projectId = DataFixtures::USER_1_PROJECT_1_ID;
        $crawler = $client->request('GET', "/projects/$projectId/cookbook");

        self::assertResponseIsSuccessful();

        $recipesInCollectionCount = count((new StaticRecipesCollection())->all());
        self::assertCount($recipesInCollectionCount, $crawler->filter('.dashboard-projects .col'));
    }
}
