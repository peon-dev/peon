<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\PhpApplication;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\PhpApplication\BuildApplication;
use Peon\Domain\PhpApplication\Value\BuildConfiguration;
use Peon\Domain\Tools\Composer\Composer;
use PHPUnit\Framework\TestCase;

class BuildApplicationTest extends TestCase
{
    public function testComposerWillBeInstalled(): void
    {
        $jobId = new JobId('');
        $workingDirectory = '/';
        $configuration = BuildConfiguration::createDefault();

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::once())
            ->method('install')
            ->with($jobId, $workingDirectory);

        $buildApplication = new BuildApplication($composer);
        $buildApplication->build($jobId, $workingDirectory, $configuration);
    }


    public function testShouldSkipComposerInstallWhenConfiguredSo(): void
    {
        $jobId = new JobId('');
        $workingDirectory = '/';

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::never())
            ->method('install');

        $configuration = new BuildConfiguration(true);

        $buildApplication = new BuildApplication($composer);
        $buildApplication->build($jobId, $workingDirectory, $configuration);
    }
}
