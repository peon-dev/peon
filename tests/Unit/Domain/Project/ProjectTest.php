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
    public function testEnableRecipe(): void
    {
        $project = new Project(
            new ProjectId(''),
            new RemoteGitRepository(
                'https://gitlab.com/phpmate-dogfood/rector.git',
                GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
            )
        );

        $project->enableRecipe(new RecipeName('test'));

        $this->expectException(RecipeAlreadyEnabledForProject::class);

        $project->enableRecipe(new RecipeName('test'));
    }


    public function testDisableRecipe(): void
    {
        $project = new Project(
            new ProjectId(''),
            new RemoteGitRepository(
                'https://gitlab.com/phpmate-dogfood/rector.git',
                GitRepositoryAuthentication::fromPersonalAccessToken('PAT')
            )
        );

        $project->enableRecipe(new RecipeName('test'));
        $project->disableRecipe(new RecipeName('test'));

        $this->expectException(RecipeNotEnabledForProject::class);

        $project->disableRecipe(new RecipeName('test'));
    }
}
