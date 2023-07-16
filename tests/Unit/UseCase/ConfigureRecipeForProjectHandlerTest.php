<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\UseCase\ConfigureRecipeForProject;
use Peon\UseCase\ConfigureRecipeForProjectHandler;
use PHPUnit\Framework\TestCase;

class ConfigureRecipeForProjectHandlerTest extends TestCase
{
    public function test(): void
    {
        $project = $this->createMock(Project::class);
        $project->expects(self::once())
            ->method('configureRecipe')
            ->with(
                RecipeName::TYPED_PROPERTIES,
                new RecipeJobConfiguration(true, 'ls -la'),
            );

        $projectsCollection = $this->createMock(ProjectsCollection::class);
        $projectsCollection->expects(self::once())
            ->method('get')
            ->willReturn($project);
        $projectsCollection->expects(self::once())
            ->method('save');

        $handler = new ConfigureRecipeForProjectHandler($projectsCollection);
        $handler->__invoke(
            new ConfigureRecipeForProject(
                new ProjectId(''),
                RecipeName::TYPED_PROPERTIES,
                new RecipeJobConfiguration(
                    true,
                    'ls -la',
                ),
            ),
        );
    }
}
