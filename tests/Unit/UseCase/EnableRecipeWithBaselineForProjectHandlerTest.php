<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Cookbook\Event\RecipeEnabled;
use Peon\Domain\GitProvider\GitProvider;
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
use Peon\Domain\User\Value\UserId;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\EnableRecipeWithBaselineForProject;
use Peon\UseCase\EnableRecipeWithBaselineForProjectHandler;
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

        $project = $this->createTestProxy(Project::class, [
                new ProjectId(''),
                new RemoteGitRepository('https://gitlab.com/peon/peon.git', GitRepositoryAuthentication::fromPersonalAccessToken('PAT')),
                new UserId(''),
            ]);
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

        $gitProvider = $this->createMock(GitProvider::class);
        $gitProvider->expects(self::once())
            ->method('getLastCommitOfDefaultBranch')
            ->willReturn(new Commit('abcd'));

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RecipeEnabled::class));

        $handler = new EnableRecipeWithBaselineForProjectHandler(
            $projectsCollection,
            $recipesCollection,
            $gitProvider,
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

        $gitProvider = $this->createMock(GitProvider::class);

        $dummyEventBus = $this->createMock(EventBus::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new EnableRecipeWithBaselineForProjectHandler(
            $projectsCollection,
            $recipesCollection,
            $gitProvider,
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

        $gitProvider = $this->createMock(GitProvider::class);

        $dummyEventBus = $this->createMock(EventBus::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new EnableRecipeWithBaselineForProjectHandler($projectsCollection,
            $recipesCollection,
            $gitProvider,
            $dummyEventBus,
        );

        $handler->__invoke($command);
    }
}
