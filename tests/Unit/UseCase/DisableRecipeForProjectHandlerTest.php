<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Cookbook\Event\RecipeDisabled;
use Peon\Domain\Cookbook\Event\RecipeEnabled;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\UseCase\DisableRecipeForProject;
use Peon\UseCase\DisableRecipeForProjectHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

final class DisableRecipeForProjectHandlerTest extends TestCase
{
    public function testRecipeCanBeDisabled(): void
    {
        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new DisableRecipeForProject(
            $recipeName,
            $projectId,
        );

        $project = $this->createMock(Project::class);
        $project->expects(self::once())
            ->method('disableRecipe')
            ->with($recipeName);

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->expects(self::once())
            ->method('save');

        $projectsCollection
            ->expects(self::once())
            ->method('get')
            ->willReturn($project);

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $eventBusSpy = $this->createMock(EventBus::class);
        $eventBusSpy->expects(self::once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RecipeDisabled::class));

        $handler = new DisableRecipeForProjectHandler($projectsCollection, $recipesCollection, $eventBusSpy);
        $handler->__invoke($command);
    }


    public function testProjectNotFound(): void
    {
        $this->expectException(ProjectNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new DisableRecipeForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $dummyEventBus = $this->createMock(EventBus::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new DisableRecipeForProjectHandler($projectsCollection, $recipesCollection, $dummyEventBus);

        $handler->__invoke($command);
    }


    public function testRecipeNotFound(): void
    {
        $this->expectException(RecipeNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new DisableRecipeForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->expects(self::once())
            ->method('hasRecipeWithName')
            ->with($recipeName)
            ->willReturn(false);

        $dummyEventBus = $this->createMock(EventBus::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new DisableRecipeForProjectHandler($projectsCollection, $recipesCollection, $dummyEventBus);

        $handler->__invoke($command);
    }


    public function testRecipeNotEnabled(): void
    {
        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new DisableRecipeForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $project = $this->createMock(Project::class);
        $project->expects(self::once())
            ->method('disableRecipe');

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->method('get')
            ->willReturn($project);

        $dummyEventBus = $this->createMock(EventBus::class);

        $handler = new DisableRecipeForProjectHandler($projectsCollection, $recipesCollection, $dummyEventBus);

        $handler->__invoke($command);
    }
}
