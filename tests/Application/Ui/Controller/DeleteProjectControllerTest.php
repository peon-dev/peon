<?php

declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Project\ProjectsCollection;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DeleteProjectControllerTest extends WebTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomProjectId = Uuid::uuid4()->toString();

        $client->request('GET', "/delete-project/$randomProjectId");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/delete-project/$projectId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectsCountBeforeScenario = count($projectsCollection->all());

        $projectId = DataFixtures::PROJECT_1_ID;
        $client->request('GET', "/delete-project/$projectId");

        self::assertResponseRedirects('/');
        self::assertCount($projectsCountBeforeScenario - 1, $projectsCollection->all());
    }
}
