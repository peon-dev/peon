<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Composer;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\Composer\ComposerJsonFileMissing;
use PHPMate\Domain\FileSystem\WorkingDirectory;
use PHPUnit\Framework\TestCase;

class ComposerTest extends TestCase
{
    public function testInstallInWorkingDirectoryWillThrowExceptionWhenJsonFileMissing(): void
    {
        $this->expectException(ComposerJsonFileMissing::class);

        $composerBinary = $this->createStub(ComposerBinary::class);
        $projectDirectory = $this->getWorkingDirectory(false);

        $composer = new Composer($composerBinary);
        $composer->install($projectDirectory);
    }


    public function testInstallInWorkingDirectory(): void
    {
        $projectDirectory = $this->getWorkingDirectory(true);

        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                $projectDirectory,
                'install --ignore-platform-reqs --no-scripts --no-interaction'
            );

        $composer = new Composer($composerBinary);
        $composer->install($projectDirectory);
    }


    private function getWorkingDirectory(bool $composerJsonFileExists): WorkingDirectory
    {
        $projectDirectory = $this->createMock(WorkingDirectory::class);
        $projectDirectory->method('fileExists')
            ->with('composer.json')
            ->willReturn($composerJsonFileExists);

        return $projectDirectory;
    }
}
