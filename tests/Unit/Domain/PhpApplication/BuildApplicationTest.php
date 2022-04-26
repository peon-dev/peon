<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\PhpApplication;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\PhpApplication\BuildPhpApplication;
use Peon\Domain\PhpApplication\Value\PhpApplicationBuildConfiguration;
use Peon\Domain\Tools\Composer\Composer;
use PHPUnit\Framework\TestCase;

class BuildApplicationTest extends TestCase
{
    public function testComposerWillBeInstalled(): void
    {
        $jobId = new JobId('');
        $workingDirectory = '/';
        $configuration = PhpApplicationBuildConfiguration::createDefault();

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::once())
            ->method('install')
            ->with($jobId, $workingDirectory);

        $buildApplication = new BuildPhpApplication($composer);
        $buildApplication->build($jobId, $workingDirectory, $configuration);
    }


    public function testShouldSkipComposerInstallWhenConfiguredSo(): void
    {
        $jobId = new JobId('');
        $workingDirectory = '/';

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::never())
            ->method('install');

        $configuration = new PhpApplicationBuildConfiguration(true);

        $buildApplication = new BuildPhpApplication($composer);
        $buildApplication->build($jobId, $workingDirectory, $configuration);
    }
}
