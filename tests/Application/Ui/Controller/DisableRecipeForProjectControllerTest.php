<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DisableRecipeForProjectControllerTest extends WebTestCase
{
    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';
        $recipeName = RecipeName::TYPED_PROPERTIES;

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/disable");

        self::assertResponseStatusCodeSame(404);
    }


    public function testNonExistingRecipeWillShow404(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = 'something-not-existing';

        $client->request('GET', "/projects/$projectId/recipe/$recipeName/disable");

        self::assertResponseStatusCodeSame(404);
    }


    public function testAlreadyDisabledRecipeWillChangeNothing(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $projectsCollection = $container->get(ProjectsCollection::class);
        $projectId = DataFixtures::PROJECT_1_ID;
        $recipeName = RecipeName::SWITCH_TO_MATCH;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesCountBeforeScenario = $project->enabledRecipes;

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/disable");

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
        $recipeName = RecipeName::TYPED_PROPERTIES;

        $project = $projectsCollection->get(new ProjectId($projectId));
        $enabledRecipesCountBeforeScenario = $project->enabledRecipes;

        $client->request('GET', "/projects/$projectId/recipe/$recipeName->value/disable");

        self::assertResponseRedirects("/projects/$projectId/cookbook");

        $project = $projectsCollection->get(new ProjectId($projectId));
        self::assertCount(count($enabledRecipesCountBeforeScenario) - 1, $project->enabledRecipes);
    }
}
