<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Composer;

use League\Flysystem\FilesystemReader;
use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\Composer\ComposerJsonFileMissing;
use PHPUnit\Framework\TestCase;

class ComposerTest extends TestCase
{
    public function testInstallInDirectoryWillThrowExceptionWhenJsonFileMissing(): void
    {
        $this->expectException(ComposerJsonFileMissing::class);

        $composerBinary = $this->createStub(ComposerBinary::class);

        $composer = new Composer($composerBinary);

        $filesystem = $this->createMock(FilesystemReader::class);
        $filesystem->method('fileExists')
            ->with('composer.json')
            ->willReturn(false);

        $composer->installInDirectory($filesystem);
    }

    public function testInstallInDirectory(): void
    {
        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('exec')
            ->with('install');

        $composer = new Composer($composerBinary);

        $filesystem = $this->createStub(FilesystemReader::class);
        $filesystem->method('fileExists')->willReturn(true);

        $composer->installInDirectory($filesystem);
    }
}
