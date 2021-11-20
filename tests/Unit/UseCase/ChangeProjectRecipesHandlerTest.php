<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\UseCase;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\UseCase\ChangeProjectRecipes;
use PHPMate\UseCase\ChangeProjectRecipesHandler;
use PHPUnit\Framework\TestCase;

final class ChangeProjectRecipesHandlerTest extends TestCase
{
    public function testRecipeCanBeEnabled(): void
    {
        $projectId = new ProjectId('');
        $recipes = [
            RecipeName::TYPED_PROPERTIES(),
            RecipeName::UNUSED_PRIVATE_METHODS(),
        ];

        $command = new ChangeProjectRecipes($projectId, $recipes);

        $project = $this->createMock(Project::class);
        $project->expects(self::once())
            ->method('changeRecipes')
            ->with($recipes);

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->expects(self::once())
            ->method('save');

        $projectsCollection
            ->expects(self::once())
            ->method('get')
            ->willReturn($project);

        $handler = new ChangeProjectRecipesHandler($projectsCollection);
        $handler->__invoke($command);
    }
}
