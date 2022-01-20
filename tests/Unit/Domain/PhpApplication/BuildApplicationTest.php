<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\PhpApplication;

use Peon\Domain\PhpApplication\BuildApplication;
use Peon\Domain\PhpApplication\Value\BuildConfiguration;
use Peon\Domain\Tools\Composer\Composer;
use PHPUnit\Framework\TestCase;

class BuildApplicationTest extends TestCase
{
    public function testComposerWillBeInstalled(): void
    {
        $workingDirectory = '/';
        $composer = $this->createMock(Composer::class);
        $composer->expects(self::once())
            ->method('install')
            ->with($workingDirectory);

        $configuration = new BuildConfiguration(false);

        $buildApplication = new BuildApplication($composer);
        $buildApplication->build($workingDirectory, $configuration);
    }


    public function testShouldSkipComposerInstallWhenConfiguredSo(): void
    {
        $workingDirectory = '/';
        $composer = $this->createMock(Composer::class);
        $composer->expects(self::never())
            ->method('install');

        $configuration = new BuildConfiguration(true);

        $buildApplication = new BuildApplication($composer);
        $buildApplication->build($workingDirectory, $configuration);
    }
}
