<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Job;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\GetPathsToProcess;
use Peon\Domain\Job\GetRecipeCommands;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Project\Value\RecipeJobConfiguration;
use Peon\Tests\DataFixtures\TestDataFactory;
use PHPUnit\Framework\TestCase;

final class GetRecipeCommandsTest extends TestCase
{
    public function testNoCommandsWillBeReturnedWithoutPaths(): void
    {
        $getPathsToProcessMock = $this->createMock(GetPathsToProcess::class);
        $getPathsToProcessMock->expects($this->once())
            ->method('forJob')
            ->willReturn([]);

        $enabledRecipe = new EnabledRecipe(
            RecipeName::OBJECT_MAGIC_CLASS_CONSTANT,
            null,
            RecipeJobConfiguration::createDefault(),
        );

        $handler = new GetRecipeCommands($getPathsToProcessMock);
        $commands = $handler->forApplication($enabledRecipe, TestDataFactory::createTemporaryApplication());

        $this->assertSame([], $commands);
    }

    public function testWillReturnRecipeCommandsForPath(): void
    {
        $getPathsToProcessMock = $this->createMock(GetPathsToProcess::class);
        $getPathsToProcessMock->expects($this->once())
            ->method('forJob')
            ->willReturn(['path']);

        $enabledRecipe = new EnabledRecipe(
            RecipeName::OBJECT_MAGIC_CLASS_CONSTANT,
            null,
            RecipeJobConfiguration::createDefault(),
        );

        $handler = new GetRecipeCommands($getPathsToProcessMock);
        $commands = $handler->forApplication($enabledRecipe, TestDataFactory::createTemporaryApplication());

        $this->assertSame(['/peon/bin/run-recipe object-magic-class-constant path'], $commands);
    }

    public function testWithAfterScriptWillReturnCommand(): void
    {
        $getPathsToProcessMock = $this->createMock(GetPathsToProcess::class);
        $getPathsToProcessMock->expects($this->once())
            ->method('forJob')
            ->willReturn(['path']);

        $enabledRecipe = new EnabledRecipe(
            RecipeName::OBJECT_MAGIC_CLASS_CONSTANT,
            null,
            new RecipeJobConfiguration(
                true,
                'after script'
            ),
        );

        $handler = new GetRecipeCommands($getPathsToProcessMock);
        $commands = $handler->forApplication($enabledRecipe, TestDataFactory::createTemporaryApplication());

        $this->assertSame(['/peon/bin/run-recipe object-magic-class-constant path', 'after script'], $commands);
    }
}
