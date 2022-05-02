<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Tools\Composer;

use Peon\Domain\Container\DetectContainerImage;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Tools\Composer\Composer;
use Peon\Tests\DataFixtures\TestDataFactory;
use PHPUnit\Framework\TestCase;

class ComposerTest extends TestCase
{
    public function testInstall(): void
    {
        $application = TestDataFactory::createTemporaryApplication();
        $image = '';

        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inContainer')
            ->with(
                $application->jobId,
                $image,
                $application->gitRepository->workingDirectory->hostPath,
                'composer install --no-interaction --ignore-platform-reqs',
                2 * 60
            );

        $detectContainerImage = $this->createMock(DetectContainerImage::class);
        $detectContainerImage->expects(self::once())
            ->method('forLanguage')
            ->willReturn($image);

        $composer = new Composer($executeCommand, $detectContainerImage);
        $composer->install($application);
    }


    public function testGetPsr4Roots(): void
    {
        $this->markTestIncomplete();
    }
}
