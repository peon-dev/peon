<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Rector;

use League\Flysystem\FilesystemReader;
use PHPMate\Domain\Rector\Rector;
use PHPMate\Domain\Rector\RectorBinary;
use PHPMate\Domain\Rector\RectorConfigFileMissing;
use PHPUnit\Framework\TestCase;

class RectorTest extends TestCase
{
    public function testRunInDirectory(): void
    {
        $workingDirectory = '.';
        $filesystemReader = $this->getFilesystemReader(true);

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('execInDirectory')
            ->with(
                $workingDirectory,
                'process'
            );

        $rector = new Rector($rectorBinary, $filesystemReader);
        $rector->runInDirectory($workingDirectory);
    }

    public function testRunInDirectoryWillThrowExceptionWhenConfigMissing(): void
    {
        $this->expectException(RectorConfigFileMissing::class);

        $rectorBinary = $this->createStub(RectorBinary::class);
        $filesystemReader = $this->getFilesystemReader(false);

        $rector = new Rector($rectorBinary, $filesystemReader);
        $rector->runInDirectory('.');
    }


    private function getFilesystemReader(bool $rectorConfigFileExists): FilesystemReader
    {
        $filesystemReader = $this->createMock(FilesystemReader::class);
        $filesystemReader->method('fileExists')
            ->with('./rector.php')
            ->willReturn($rectorConfigFileExists);

        return $filesystemReader;
    }
}
