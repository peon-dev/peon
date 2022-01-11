<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Cookbook\Event\RecipeEnabled;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Cookbook\Exception\RecipeNotFound;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use PHPMate\UseCase\EnableRecipeForProject;
use PHPMate\UseCase\EnableRecipeForProjectHandler;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

class EnableRecipeForProjectHandlerTest extends TestCase
{
    public function testRecipeCanBeEnabled(): void
    {
        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new EnableRecipeForProject(
            $recipeName,
            $projectId,
        );

        $project = $this->createMock(Project::class);
        $project->expects(self::once())
            ->method('enableRecipe')
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
            ->with(new IsInstanceOf(RecipeEnabled::class));

        $handler = new EnableRecipeForProjectHandler(
            $projectsCollection,
            $recipesCollection,
            $eventBusSpy
        );
        $handler->__invoke($command);
    }


    public function testProjectNotFound(): void
    {
        $this->expectException(ProjectNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new EnableRecipeForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $dummyEventBus = $this->createMock(EventBus::class);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new EnableRecipeForProjectHandler($projectsCollection, $recipesCollection, $dummyEventBus);

        $handler->__invoke($command);
    }


    public function testRecipeNotFound(): void
    {
        $this->expectException(RecipeNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new EnableRecipeForProject(
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
        $handler = new EnableRecipeForProjectHandler($projectsCollection, $recipesCollection, $dummyEventBus);

        $handler->__invoke($command);
    }


    public function testRecipeAlreadyEnabled(): void
    {
        $recipeName = RecipeName::TYPED_PROPERTIES;
        $projectId = new ProjectId('');
        $command = new EnableRecipeForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $project = $this->createMock(Project::class);
        $project->expects(self::once())
            ->method('enableRecipe');

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->method('get')
            ->willReturn($project);

        $dummyEventBus = $this->createMock(EventBus::class);

        $handler = new EnableRecipeForProjectHandler($projectsCollection, $recipesCollection, $dummyEventBus);

        $handler->__invoke($command);
    }
}
