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
    public function testInstallInDirectoryWillThrowExceptionWhenJsonFileMissing(): void
    {
        $this->expectException(ComposerJsonFileMissing::class);

        $composerBinary = $this->createStub(ComposerBinary::class);

        $composer = new Composer($composerBinary);

        $workingDirectory = $this->createMock(WorkingDirectory::class);
        $workingDirectory->method('fileExists')
            ->with('composer.json')
            ->willReturn(false);

        $composer->installInDirectory($workingDirectory);
    }

    public function testInstallInDirectory(): void
    {
        $workingDirectory = $this->createMock(WorkingDirectory::class);
        $workingDirectory->method('fileExists')->willReturn(true);

        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('execInDirectory')
            ->with(
                $workingDirectory,
                'install'
            );

        $composer = new Composer($composerBinary);
        $composer->installInDirectory($workingDirectory);
    }
}
