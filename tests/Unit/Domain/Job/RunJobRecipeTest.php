<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Job;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\RunJobRecipe;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\Tools\Git\Git;
use Peon\Domain\Tools\Rector\Rector;
use PHPUnit\Framework\TestCase;

class RunJobRecipeTest extends TestCase
{
    /**
     * @dataProvider provideTestAllConfigurationFilesExistsData
     */
    public function testAllConfigurationFilesExists(EnabledRecipe $enabledRecipe): void
    {
        $jobId = new JobId('');

        $rector = $this->createMock(Rector::class);
        $rector->expects(self::once())
            ->method('process');

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::once())
            ->method('getPsr4Roots')
            ->willReturn(['/some-path']);

        $git = $this->createMock(Git::class);

        $runJobRecipe = new RunJobRecipe(
            $rector,
            $composer,
            $git,
        );

        $runJobRecipe->run($jobId, $enabledRecipe, '/');
    }


    /**
     * @return \Generator<array{EnabledRecipe}>
     */
    public function provideTestAllConfigurationFilesExistsData(): \Generator
    {
        foreach (RecipeName::cases() as $recipeName) {
            yield [new EnabledRecipe($recipeName, null)];
        }
    }
}
