<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Job;

use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Job\RunJobRecipe;
use PHPMate\Domain\Tools\Composer\Composer;
use PHPMate\Domain\Tools\Rector\Rector;
use PHPUnit\Framework\TestCase;

class RunJobRecipeTest extends TestCase
{
    /**
     * @dataProvider provideTestAllConfigurationFilesExistsData
     */
    public function testAllConfigurationFilesExists(RecipeName $recipeName): void
    {
        $rector = $this->createMock(Rector::class);
        $rector->expects(self::once())
            ->method('process');

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::once())
            ->method('getPsr4Roots')
            ->willReturn([]);

        $runJobRecipe = new RunJobRecipe(
            $rector,
            $composer,
        );

        $runJobRecipe->run($recipeName, '/');
    }


    /**
     * @return \Generator<array{RecipeName}>
     */
    public function provideTestAllConfigurationFilesExistsData(): \Generator
    {
        foreach (RecipeName::cases() as $recipeName) {
            yield [$recipeName];
        }
    }
}
