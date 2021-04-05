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

        $filesystemReader = $this->createMock(FilesystemReader::class);
        $filesystemReader->method('fileExists')
            ->with('./composer.json')
            ->willReturn(false);

        $composer = new Composer($composerBinary, $filesystemReader);

        $composer->installInDirectory('.');
    }

    public function testInstallInDirectory(): void
    {
        $workingDirectory = '.';

        $filesystemReader = $this->createMock(FilesystemReader::class);
        $filesystemReader->method('fileExists')
            ->with('./composer.json')
            ->willReturn(true);

        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('execInDirectory')
            ->with(
                $workingDirectory,
                'install'
            );

        $composer = new Composer($composerBinary, $filesystemReader);
        $composer->installInDirectory($workingDirectory);
    }
}
