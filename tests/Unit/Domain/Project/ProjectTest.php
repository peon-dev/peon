<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Project;

use PHPMate\Domain\Project\Exceptions\RecipeAlreadyEnabledForProject;
use PHPMate\Domain\Cookbook\RecipeName;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\Exceptions\RecipeNotEnabledForProject;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Tests\DataFixtures\DataFixtures;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function testProjectNameWillBeTakenFromRepository(): void
    {
        $project = $this->createProject();

        self::assertSame(DataFixtures::PROJECT_NAME, $project->name);
    }


    public function testEnableRecipe(): void
    {
        $project = $this->createProject();

        self::assertCount(0, $project->enabledRecipes);

        $recipeName = new RecipeName('test');
        $project->enableRecipe($recipeName);

        self::assertCount(1, $project->enabledRecipes);
        self::assertTrue($recipeName->isEqual($project->enabledRecipes[0]));

        $this->expectException(\PHPMate\Domain\Project\Exceptions\RecipeAlreadyEnabledForProject::class);
        $project->enableRecipe($recipeName);
    }


    public function testDisableRecipe(): void
    {
        $project = $this->createProject();
        $toBeDisabledRecipeName = new RecipeName('to-be-removed');

        $project->enableRecipe($toBeDisabledRecipeName);
        $project->enableRecipe(new RecipeName('test'));
        self::assertCount(2, $project->enabledRecipes);
        self::assertTrue($toBeDisabledRecipeName->isEqual($project->enabledRecipes[array_key_first($project->enabledRecipes)]));

        $project->disableRecipe($toBeDisabledRecipeName);
        self::assertCount(1, $project->enabledRecipes);
        self::assertFalse($toBeDisabledRecipeName->isEqual($project->enabledRecipes[array_key_first($project->enabledRecipes)]));

        $this->expectException(RecipeNotEnabledForProject::class);
        $project->disableRecipe($toBeDisabledRecipeName);
    }


    private function createProject(): Project
    {
        return new Project(
            new ProjectId(''),
            DataFixtures::createRemoteGitRepository()
        );
    }
}
