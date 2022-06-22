<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectSettingsControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomProjectId = Uuid::uuid4()->toString();

        $client->request('GET', "/projects/$randomProjectId/settings");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testCanNotAccessForeignProject(): void
    {
        $client = self::createClient();
        $anotherUserProjectId = DataFixtures::USER_2_PROJECT_1_ID;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        foreach (['GET', 'POST'] as $method) {
            $client->request($method, "/projects/$anotherUserProjectId/settings");

            // Intentionally 404, and not 401/403
            self::assertResponseStatusCodeSame(404);
        }
    }


    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/settings");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/settings");

        self::assertResponseIsSuccessful();
    }


    public function testProjectCanBeConfiguredBySubmittingForm(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;

        // Check it was false before
        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertFalse($project->buildConfiguration->skipComposerInstall);

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

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
