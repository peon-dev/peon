<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Project\ProjectsCollection;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateProjectControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $client->request('GET', '/add-project');

        self::assertResponseRedirects('/login');
    }


    public function testDuplicateProjectCanNotBeAdded(): void
    {
        $this->markTestIncomplete('To be implemented');
    }


    public function testProjectWillBeAddedUsingForm(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectsCountBeforeScenario = count($projectsCollection->all());

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', '/add-project');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('submit')->form();

        $client->submit($form, [
            $form->getName() . '[remoteRepositoryUri]' => 'https://gitlab.com/peon/peon.git',
            $form->getName() . '[personalAccessToken]' => '...',
        ]);

        self::assertResponseRedirects('/');

        self::assertCount(1 + $projectsCountBeforeScenario, $projectsCollection->all());
    }
}
