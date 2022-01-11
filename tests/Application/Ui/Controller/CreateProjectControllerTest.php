<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Project\ProjectsCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateProjectControllerTest extends WebTestCase
{
    public function testProjectWillBeAddedUsingForm(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectsCountBeforeScenario = count($projectsCollection->all());

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
