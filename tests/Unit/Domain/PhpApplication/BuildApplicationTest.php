<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\PhpApplication;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\PhpApplication\BuildPhpApplication;
use Peon\Domain\PhpApplication\Value\PhpApplicationBuildConfiguration;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Tests\DataFixtures\TestDataFactory;
use PHPUnit\Framework\TestCase;

class BuildApplicationTest extends TestCase
{
    public function testComposerWillBeInstalled(): void
    {
        $application = TestDataFactory::createTemporaryApplication();

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::once())
            ->method('install')
            ->with($application);

        $configuration = PhpApplicationBuildConfiguration::createDefault();

        $buildApplication = new BuildPhpApplication($composer);
        $buildApplication->build($application, $configuration);
    }


    public function testShouldSkipComposerInstallWhenConfiguredSo(): void
    {
        $application = TestDataFactory::createTemporaryApplication();

        $composer = $this->createMock(Composer::class);
        $composer->expects(self::never())
            ->method('install');

        $configuration = new PhpApplicationBuildConfiguration(true);

        $buildApplication = new BuildPhpApplication($composer);
        $buildApplication->build($application, $configuration);
    }
}
