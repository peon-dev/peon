<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Exception\RecipeNotEnabled;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ConfigureRecipeControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomRecipeName = 'random-recipe-name';
        $randomProjectId = Uuid::uuid4()->toString();

        $client->request('GET', "/projects/$randomProjectId/configure-recipe/$randomRecipeName");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testCanNotConfigureRecipeForForeignProject(): void
    {
        $client = self::createClient();
        $anotherUserProjectId = DataFixtures::USER_2_PROJECT_1_ID;
        $recipeName = RecipeName::TYPED_PROPERTIES->value;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        foreach (['GET', 'POST'] as $method) {
            $client->request($method, "/projects/$anotherUserProjectId/configure-recipe/$recipeName");

            // Intentionally 404, and not 401/403
            self::assertResponseStatusCodeSame(404);
        }
    }


    public function testNotFoundProjectWillBe404(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $recipeName = RecipeName::TYPED_PROPERTIES->value;
        $projectId = Uuid::uuid4()->toString();

        $client->request('GET', "/projects/$projectId/configure-recipe/$recipeName");

        self::assertResponseStatusCodeSame(404);
    }


    public function testUnknownRecipeWillRedirectToProjectOverview(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $projectId = DataFixtures::USER_1_PROJECT_1_ID;

        $client->request('GET', "/projects/$projectId/configure-recipe/unknown-recipe");

        self::assertResponseRedirects("/projects/$projectId");
    }


    public function testDisabledRecipeWillRedirectToProjectOverview(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;
        $project = $projectsCollection->get(new ProjectId($projectId));
        $recipeName = RecipeName::OBJECT_MAGIC_CLASS_CONSTANT->value;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $exception = null;
        $enabledRecipe = null;
        try {
            $enabledRecipe = $project->getEnabledRecipe(RecipeName::OBJECT_MAGIC_CLASS_CONSTANT);
        } catch (RecipeNotEnabled $exception) {
        }
        self::assertNotNull($exception);
        self::assertNull($enabledRecipe);

        $crawler = $client->request('GET', "/projects/$projectId/configure-recipe/$recipeName");

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('submit')->form();

        $client->submit($form, [
            $form->getName() . '[mergeAutomatically]' => true,
        ]);

        self::assertResponseRedirects("/projects/$projectId");

        $project = $projectsCollection->get(new ProjectId($projectId));

        $exception = null;
        $enabledRecipe = null;
        try {
            $enabledRecipe = $project->getEnabledRecipe(RecipeName::OBJECT_MAGIC_CLASS_CONSTANT);
        } catch (RecipeNotEnabled $exception) {
        }
        self::assertNotNull($exception);
        self::assertNull($enabledRecipe);
    }


    public function testPageCanBeRenderedWithoutFormSubmission(): void
    {
        $client = self::createClient();

        $projectId = DataFixtures::USER_1_PROJECT_1_ID;
        $recipeName = RecipeName::TYPED_PROPERTIES->value;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/configure-recipe/$recipeName");

        self::assertResponseIsSuccessful();
    }


    public function testRecipeCanBeConfigured(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;
        $recipeName = RecipeName::TYPED_PROPERTIES->value;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', "/projects/$projectId/configure-recipe/$recipeName");

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('submit')->form();

        self::assertCheckboxNotChecked($form->getName() . '[mergeAutomatically]');

        $client->submit($form, [
            $form->getName() . '[mergeAutomatically]' => true,
        ]);

        self::assertResponseRedirects("/projects/$projectId");

        $client->request('GET', "/projects/$projectId/configure-recipe/$recipeName");

        self::assertCheckboxChecked($form->getName() . '[mergeAutomatically]');
    }
}
