<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Cookbook\Event\RecipeEnabled;
use Peon\Domain\GitProvider\GetLastCommitOfDefaultBranch;
use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\EnableRecipeWithBaselineForProject;
use Peon\UseCase\EnableRecipeWithBaselineForProjectHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class EnableRecipeWithBaselineForProjectHandlerTest extends TestCase
{
    public function testRecipeCanBeEnabled(): void
    {
        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new EnableRecipeWithBaselineForProject(
            $recipeName,
            $projectId,
        );

        $project = $this->getMockBuilder(Project::class)
            ->setConstructorArgs([
                new ProjectId(''),
                new RemoteGitRepository('https://gitlab.com/peon/peon.git', GitRepositoryAuthentication::fromPersonalAccessToken('PAT'))
            ])
            ->getMock();
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
            ->method('forRemoteGitRepository')
            ->willReturn(new Commit('abcd'));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with(new IsInstanceOf(RecipeEnabled::class));

        $handler = new EnableRecipeWithBaselineForProjectHandler(
            $projectsCollection,
            $recipesCollection,
            $getLastCommitOfDefaultBranch,
            $eventBusSpy,
        );
        $handler->__invoke($command);
    }


    public function testProjectNotFound(): void
    {
        $this->expectException(ProjectNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new EnableRecipeWithBaselineForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $getLastCommitOfDefaultBranch = $this->createMock(GetLastCommitOfDefaultBranch::class);

        $dummyEventBus = $this->createMock(EventBus::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new EnableRecipeWithBaselineForProjectHandler(
            $projectsCollection,
            $recipesCollection,
            $getLastCommitOfDefaultBranch,
            $dummyEventBus
        );

        $handler->__invoke($command);
    }


    public function testRecipeNotFound(): void
    {
        $this->expectException(RecipeNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES;
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

        $dummyEventBus = $this->createMock(EventBus::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new EnableRecipeWithBaselineForProjectHandler($projectsCollection,
            $recipesCollection,
            $getLastCommitOfDefaultBranch,
            $dummyEventBus,
        );

        $handler->__invoke($command);
    }
}
