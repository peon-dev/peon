<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EnableRecipeForProjectControllerTest extends WebTestCase
{
    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';
        $recipeName = RecipeName::SWITCH_TO_MATCH;

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable");

        self::assertResponseStatusCodeSame(404);
    }


    public function testNonExistingRecipeWillShow404(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = 'something-not-existing';

        $client->request('GET', "/projects/$projectId/recipe/$recipeName/enable");

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
        $enabledRecipesCountBeforeScenario = $project->enabledRecipes;

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable");

        self::assertResponseRedirects("/projects/$projectId/cookbook");

        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertCount(count($enabledRecipesCountBeforeScenario), $project->enabledRecipes);
    }


    public function testRecipeWillBeEnabled(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = RecipeName::SWITCH_TO_MATCH;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesCountBeforeScenario = $project->enabledRecipes;

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/enable");

        self::assertResponseRedirects("/projects/$projectId/cookbook");

        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertCount(1 + count($enabledRecipesCountBeforeScenario), $project->enabledRecipes);
    }
}
