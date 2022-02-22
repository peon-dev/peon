<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\UseCase;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Tests\DataFixtures\DataFixtures;
use Peon\UseCase\ConfigureRecipeForProject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigureRecipeForProjectHandlerIntegrationTest extends KernelTestCase
{
    public function test(): void
    {
        $container = self::getContainer();
        $bus = $container->get(CommandBus::class);
        $projectId = new ProjectId(DataFixtures::PROJECT_1_ID);

        $bus->dispatch(
            new ConfigureRecipeForProject(
                $projectId,
                RecipeName::UNUSED_PRIVATE_METHODS,
                true,
            )
        );

        $projectsCollection = $container->get(ProjectsCollection::class);
        $project = $projectsCollection->get($projectId);
        $enabledRecipe = $project->getEnabledRecipe(RecipeName::UNUSED_PRIVATE_METHODS);

        self::assertTrue($enabledRecipe?->configuration->mergeAutomatically);
    }
}
