<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EnableRecipeWithBaselineForProjectControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $recipeName = RecipeName::TYPED_PROPERTIES;
        $randomProjectId = Uuid::uuid4()->toString();

        $client->request('GET', "/projects/$randomProjectId/recipe/$recipeName->value/enable-with-baseline");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testCanNotEnableRecipeForForeignProject(): void
    {
        $client = self::createClient();
        $anotherUserProjectId = DataFixtures::USER_2_PROJECT_1_ID;
        $recipeName = RecipeName::TYPED_PROPERTIES;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$anotherUserProjectId/recipe/$recipeName->value/enable-with-baseline");

        // Intentionally 404, and not 401/403
        self::assertResponseStatusCodeSame(404);
    }


    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();

        $projectId = '00000000-0000-0000-0000-000000000000';
        $recipeName = RecipeName::SWITCH_TO_MATCH;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable-with-baseline");

        self::assertResponseStatusCodeSame(404);
    }


    public function testNonExistingRecipeWillShow404(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;
        $recipeName = 'something-not-existing';

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/recipe/$recipeName/enable-with-baseline");

        self::assertResponseStatusCodeSame(404);
    }


    public function testAlreadyEnabledRecipeWillChangeNothing(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;
        $recipeName = RecipeName::TYPED_PROPERTIES;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesBeforeScenario = $project->enabledRecipes;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable-with-baseline");

        self::assertResponseRedirects("/projects/$projectId/cookbook");

        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertCount(count($enabledRecipesBeforeScenario), $project->enabledRecipes);
    }


    public function testAlreadyEnabledRecipeWithoutBaselineWillBeAdded(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;
        $recipeName = RecipeName::UNUSED_PRIVATE_METHODS;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesBeforeScenario = $project->enabledRecipes;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable-with-baseline");

        self::assertResponseRedirects("/projects/$projectId/cookbook");

        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertCount(count($enabledRecipesBeforeScenario), $project->enabledRecipes);
    }


    public function testRecipeWillBeEnabled(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;
        $recipeName = RecipeName::SWITCH_TO_MATCH;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesBeforeScenario = $project->enabledRecipes;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable-with-baseline");

        self::assertResponseRedirects("/projects/$projectId/cookbook");

        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertCount(1 + count($enabledRecipesBeforeScenario), $project->enabledRecipes);
    }
}
