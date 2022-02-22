<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConfigureRecipeControllerTest extends WebTestCase
{
    public function testNotFoundProjectWillBe404(): void
    {
        $client = static::createClient();
        $recipeName = RecipeName::TYPED_PROPERTIES->value;

        $client->request('GET', "/projects/unknown-id/configure-recipe/$recipeName");

        self::assertResponseStatusCodeSame(404);
    }


    public function testUnknownRecipeWillRedirectToProjectOverview(): void
    {
        $client = static::createClient();
        $projectId = DataFixtures::PROJECT_1_ID;

        $client->request('GET', "/projects/$projectId/configure-recipe/unknown-recipe");

        self::assertResponseRedirects("/projects/$projectId");
    }


    public function testDisabledRecipeWillRedirectToProjectOverview(): void
    {
        $client = static::createClient();
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = RecipeName::OBJECT_MAGIC_CLASS_CONSTANT->value;

        $client->request('GET', "/projects/$projectId/configure-recipe/$recipeName");

        self::assertResponseRedirects("/projects/$projectId");
    }


    public function testPageCanBeRenderedWithoutFormSubmission(): void
    {
        $client = static::createClient();

        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = RecipeName::TYPED_PROPERTIES->value;

        $client->request('GET', "/projects/$projectId/configure-recipe/$recipeName");

        self::assertResponseIsSuccessful();
    }


    public function testRecipeCanBeConfigured(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::PROJECT_1_ID;
        $project = $projectsCollection->get(new ProjectId($projectId));
        $recipeName = RecipeName::TYPED_PROPERTIES->value;

        // $project->getEnabledRecipe(RecipeName::TYPED_PROPERTIES)?->configuration->mergeAutomatically

        $crawler = $client->request('GET', "/projects/$projectId/configure-recipe/$recipeName");

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('submit')->form();

        $client->submit($form, [
            $form->getName() . '[mergeAutomatically]' => true,
        ]);

        self::assertResponseRedirects("/projects/$projectId");
    }
}
