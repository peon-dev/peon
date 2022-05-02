<?php
declare(strict_types=1);

namespace Peon\Tests\Integration\Domain\Job;

use Nette\Utils\FileSystem;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\RunJobRecipe;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Tests\TestingRemoteGitRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;

class RunJobRecipeIntegrationTest extends KernelTestCase
{
    /**
     * @dataProvider provideRecipeNames
     */
    public function testCodeIsChangedAsExpected(RecipeName $recipeName): void
    {
        $this->markTestSkipped('Needs to be re-implemented');

        /*
        $runJobRecipe = self::getContainer()->get(RunJobRecipe::class);
        $jobId = new JobId(Uuid::uuid4()->toString());

        $testingGitRepository = TestingRemoteGitRepository::init();
        $testingGitRepository->dumpComposerAutoload();

        $runJobRecipe->run($jobId, EnabledRecipe::withoutConfiguration($recipeName, null), $testingGitRepository->directory);

        $expectationFileContent = FileSystem::read(__DIR__ . '/../../../RecipesExpectedChanges/' . $recipeName->value . '.xml');
        $xml = new \SimpleXMLElement($expectationFileContent);
        self::assertNotEmpty($xml);
        foreach ($xml->expectation as $expectation) {
            $process = Process::fromShellCommandline((string) $expectation->command, $testingGitRepository->directory);
            $process->mustRun();
            self::assertSame((string) $expectation->output, rtrim($process->getOutput()));
        }
        */
    }


    /**
     * @return \Generator<array{RecipeName}>
     */
    public function provideRecipeNames(): \Generator
    {
        foreach (RecipeName::cases() as $recipeName) {
            yield [
                $recipeName
            ];
        }
    }
}
