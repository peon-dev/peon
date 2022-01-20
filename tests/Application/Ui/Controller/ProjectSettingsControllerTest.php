<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectSettingsControllerTest extends WebTestCase
{
    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/projects/$projectId/settings");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::PROJECT_1_ID;

        $client->request('GET', "/projects/$projectId/settings");

        self::assertResponseIsSuccessful();
    }


    public function testProjectCanBeConfiguredBySubmittingForm(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::PROJECT_1_ID;

        // Check it was false before
        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertFalse($project->buildConfiguration->skipComposerInstall);

        $crawler = $client->request('GET', "/projects/$projectId/settings");

        $form = $crawler->selectButton('submit')->form();

        $client->submit($form, [
            $form->getName() . '[skipComposerInstall]' => true,
        ]);

        self::assertResponseRedirects("/projects/$projectId");

        // Check it is true after submitting
        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertTrue($project->buildConfiguration->skipComposerInstall);
    }
}
