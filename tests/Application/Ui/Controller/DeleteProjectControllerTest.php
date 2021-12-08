<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DeleteProjectControllerTest extends WebTestCase
{
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
        self::assertCount(1 - $projectsCountBeforeScenario, $projectsCollection->all());
    }
}
