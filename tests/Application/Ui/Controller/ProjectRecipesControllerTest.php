<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectRecipesControllerTest extends WebTestCase
{
    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/project/$projectId/recipes");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::PROJECT_ID;

        $crawler = $client->request('GET', "/project/$projectId/recipes");

        $form = $crawler->selectButton('submit')->form();

        $client->submit($form, [
            $form->getName() . '[recipes]' => [
                RecipeName::UNUSED_PRIVATE_METHODS()->toString(),
                RecipeName::TYPED_PROPERTIES()->toString(),
            ],
        ]);

        self::assertResponseRedirects("/project/$projectId");

        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertEquals([RecipeName::UNUSED_PRIVATE_METHODS(), RecipeName::TYPED_PROPERTIES()], $project->enabledRecipes);
    }
}
