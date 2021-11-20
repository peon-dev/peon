<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Project;

use PHPMate\Domain\Project\RecipeAlreadyEnabledForProject;
use PHPMate\Domain\Cookbook\RecipeName;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\RecipeNotEnabledForProject;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function testProjectNameWillBeTakenFromRepository(): void
    {
        $project = $this->createProject();

        self::assertSame('phpmate-dogfood/rector', $project->name);
    }


    public function testEnableRecipe(): void
    {
        $project = $this->createProject();

        self::assertCount(0, $project->enabledRecipes);

        $recipeName = new RecipeName('test');
        $project->enableRecipe($recipeName);

        self::assertCount(1, $project->enabledRecipes);
        self::assertTrue($recipeName->isEqual($project->enabledRecipes[0]));

        $this->expectException(RecipeAlreadyEnabledForProject::class);
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
            new RemoteGitRepository(
                'https://gitlab.com/phpmate-dogfood/rector.git',
                GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
            )
        );
    }
}
