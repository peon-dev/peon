<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use PHPMate\Domain\GitProvider\Value\Commit;
use PHPMate\Domain\GitProvider\Value\GitRepositoryAuthentication;
use PHPMate\Domain\GitProvider\Value\RemoteGitRepository;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Cookbook\Exception\RecipeNotFound;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\UseCase\EnableRecipeWithBaselineForProject;
use PHPMate\UseCase\EnableRecipeWithBaselineForProjectHandler;
use PHPUnit\Framework\TestCase;

class EnableRecipeWithBaselineForProjectHandlerTest extends TestCase
{
    public function testRecipeCanBeEnabled(): void
    {
        $recipeName = RecipeName::TYPED_PROPERTIES();
        $projectId = new ProjectId('');
        $command = new EnableRecipeWithBaselineForProject(
            $recipeName,
            $projectId,
        );

        $project = $this->createMock(Project::class);
        $project->remoteGitRepository = new RemoteGitRepository('https://gitlab.com/phpmate/phpmate.git', GitRepositoryAuthentication::fromPersonalAccessToken('PAT'));
        $project->expects(self::once())
            ->method('enableRecipe')
            ->with($recipeName, 'abcd');

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->expects(self::once())
            ->method('save');

        $projectsCollection
            ->expects(self::once())
            ->method('get')
            ->willReturn($project);

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $getLastCommitOfDefaultBranch = $this->createMock(GetLastCommitOfDefaultBranch::class);
        $getLastCommitOfDefaultBranch->expects(self::once())
            ->method('getLastCommitOfDefaultBranch')
            ->willReturn(new Commit('abcd'));

        $handler = new EnableRecipeWithBaselineForProjectHandler(
            $projectsCollection,
            $recipesCollection,
            $getLastCommitOfDefaultBranch
        );
        $handler->__invoke($command);
    }


    public function testProjectNotFound(): void
    {
        $this->expectException(ProjectNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES();
        $projectId = new ProjectId('');
        $command = new EnableRecipeWithBaselineForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $getLastCommitOfDefaultBranch = $this->createMock(GetLastCommitOfDefaultBranch::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new EnableRecipeWithBaselineForProjectHandler($projectsCollection, $recipesCollection, $getLastCommitOfDefaultBranch);

        $handler->__invoke($command);
    }


    public function testRecipeNotFound(): void
    {
        $this->expectException(RecipeNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES();
        $projectId = new ProjectId('');
        $command = new EnableRecipeWithBaselineForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->expects(self::once())
            ->method('hasRecipeWithName')
            ->with($recipeName)
            ->willReturn(false);

        $getLastCommitOfDefaultBranch = $this->createMock(GetLastCommitOfDefaultBranch::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new EnableRecipeWithBaselineForProjectHandler($projectsCollection, $recipesCollection, $getLastCommitOfDefaultBranch);

        $handler->__invoke($command);
    }
}
