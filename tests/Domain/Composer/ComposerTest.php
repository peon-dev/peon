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
    public function testInstallInWorkingDirectoryWillThrowExceptionWhenJsonFileMissing(): void
    {
        $this->expectException(ComposerJsonFileMissing::class);

        $composerBinary = $this->createStub(ComposerBinary::class);
        $filesystemReader = $this->getFilesystemReader(false);

        $composer = new Composer($composerBinary, $filesystemReader);
        $composer->installInWorkingDirectory('.');
    }


    public function testInstallInWorkingDirectory(): void
    {
        $workingDirectory = '.';
        $filesystemReader = $this->getFilesystemReader(true);

        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('execInWorkingDirectory')
            ->with(
                $workingDirectory,
                'install'
            );

        $composer = new Composer($composerBinary, $filesystemReader);
        $composer->installInWorkingDirectory($workingDirectory);
    }


    private function getFilesystemReader(bool $composerJsonFileExists): FilesystemReader
    {
        $filesystemReader = $this->createMock(FilesystemReader::class);
        $filesystemReader->method('fileExists')
            ->with('./composer.json')
            ->willReturn($composerJsonFileExists);

        return $filesystemReader;
    }
}
