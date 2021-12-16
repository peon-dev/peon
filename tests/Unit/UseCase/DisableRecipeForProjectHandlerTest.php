<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Cookbook\Exception\RecipeNotFound;
use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Infrastructure\Persistence\InMemory\InMemoryProjectsCollection;
use PHPMate\UseCase\DisableRecipeForProject;
use PHPMate\UseCase\DisableRecipeForProjectHandler;
use PHPUnit\Framework\TestCase;

final class DisableRecipeForProjectHandlerTest extends TestCase
{
    public function testRecipeCanBeDisabled(): void
    {
        $recipeName = RecipeName::TYPED_PROPERTIES();
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

        $handler = new DisableRecipeForProjectHandler($projectsCollection, $recipesCollection);
        $handler->__invoke($command);
    }


    public function testProjectNotFound(): void
    {
        $this->expectException(ProjectNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES();
        $projectId = new ProjectId('');
        $command = new DisableRecipeForProject(
            $recipeName,
            $projectId,
        );

        $recipesCollection = $this->createMock(RecipesCollection::class);
        $recipesCollection->method('hasRecipeWithName')->willReturn(true);

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new DisableRecipeForProjectHandler($projectsCollection, $recipesCollection);

        $handler->__invoke($command);
    }


    public function testRecipeNotFound(): void
    {
        $this->expectException(RecipeNotFound::class);

        $recipeName = RecipeName::TYPED_PROPERTIES();
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

        $projectsCollection = new InMemoryProjectsCollection();
        $handler = new DisableRecipeForProjectHandler($projectsCollection, $recipesCollection);

        $handler->__invoke($command);
    }


    public function testRecipeNotEnabled(): void
    {
        $recipeName = RecipeName::TYPED_PROPERTIES();
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

        $handler = new DisableRecipeForProjectHandler($projectsCollection, $recipesCollection);

        $handler->__invoke($command);
    }
}
