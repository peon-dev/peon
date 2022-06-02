<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EnableRecipeWithBaselineForProjectControllerTest extends WebTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $recipeName = RecipeName::TYPED_PROPERTIES;
        $randomProjectId = Uuid::uuid4()->toString();

        $client->request('GET', "/projects/$randomProjectId/recipe/$recipeName->value/enable-with-baseline");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';
        $recipeName = RecipeName::SWITCH_TO_MATCH;

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable-with-baseline");

        self::assertResponseStatusCodeSame(404);
    }


    public function testNonExistingRecipeWillShow404(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = 'something-not-existing';

        $client->request('GET', "/projects/$projectId/recipe/$recipeName/enable-with-baseline");

        self::assertResponseStatusCodeSame(404);
    }


    public function testAlreadyEnabledRecipeWillChangeNothing(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = RecipeName::TYPED_PROPERTIES;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesBeforeScenario = $project->enabledRecipes;

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
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = RecipeName::UNUSED_PRIVATE_METHODS;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesBeforeScenario = $project->enabledRecipes;

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
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = RecipeName::SWITCH_TO_MATCH;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesBeforeScenario = $project->enabledRecipes;

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable-with-baseline");

        self::assertResponseRedirects("/projects/$projectId/cookbook");

        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertCount(1 + count($enabledRecipesBeforeScenario), $project->enabledRecipes);
    }
}
