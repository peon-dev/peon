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
        $workingDirectory = $this->getWorkingDirectory(false);

        $composer = new Composer($composerBinary);
        $composer->installInWorkingDirectory($workingDirectory);
    }


    public function testInstallInWorkingDirectory(): void
    {
        $workingDirectory = $this->getWorkingDirectory(true);

        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('execInWorkingDirectory')
            ->with(
                $workingDirectory,
                'install --ignore-platform-reqs'
            );

        $composer = new Composer($composerBinary);
        $composer->installInWorkingDirectory($workingDirectory);
    }


    private function getWorkingDirectory(bool $composerJsonFileExists): WorkingDirectory
    {
        $workingDirectory = $this->createMock(WorkingDirectory::class);
        $workingDirectory->method('fileExists')
            ->with('composer.json')
            ->willReturn($composerJsonFileExists);

        return $workingDirectory;
    }
}
