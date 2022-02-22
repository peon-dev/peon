<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Job;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\GetPathsToProcess;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Value\EnabledRecipe;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\Tools\Git\Git;
use PHPUnit\Framework\TestCase;

class GetPathsToProcessTest extends TestCase
{
    public function testAllPSR4RootsWillBeReturned(): void
    {
        $git = $this->createMock(Git::class);

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::once())
            ->method('getPsr4Roots')
            ->willReturn(['root1', 'root2']);

        $getPathsToProcess = new GetPathsToProcess($git, $composer);

        $paths = $getPathsToProcess->forJob(
            new JobId(''),
            EnabledRecipe::withoutConfiguration(RecipeName::UNUSED_PRIVATE_METHODS, null),
            '/'
        );

        self::assertSame(['root1', 'root2'], $paths);
    }


    public function testOnlyChangedFilesWithinRootsWillBeReturned(): void
    {
        $jobId = new JobId('');
        $workingDirectory = '/';

        $git = $this->createMock(Git::class);
        $git->expects(self::once())
            ->method('getChangedFilesSinceCommit')
            ->with($jobId, $workingDirectory, '12345')
            ->willReturn(['root1/file1.php', 'root2/file2.php', 'root3/file3.php']);

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::once())
            ->method('getPsr4Roots')
            ->willReturn(['root1', 'root2']);

        $getPathsToProcess = new GetPathsToProcess($git, $composer);

        $paths = $getPathsToProcess->forJob(
            $jobId,
            EnabledRecipe::withoutConfiguration(RecipeName::UNUSED_PRIVATE_METHODS, '12345'),
            $workingDirectory
        );

        self::assertSame(['root1/file1.php', 'root2/file2.php'], $paths);
    }
}
