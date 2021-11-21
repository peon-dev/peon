<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Domain\Project\ProjectsCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateProjectControllerTest extends WebTestCase
{
    public function testProjectWillBeAddedUsingForm(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectsCountBeforeScenario = count($projectsCollection->all());

        $crawler = $client->request('GET', '/create-project');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('submit')->form();

        $client->submit($form, [
            $form->getName() . '[remoteRepositoryUri]' => 'https://gitlab.com/phpmate/phpmate.git',
            $form->getName() . '[personalAccessToken]' => '...',
        ]);

        self::assertResponseRedirects('/');

        self::assertCount(1 + $projectsCountBeforeScenario, $projectsCollection->all());
    }
}
