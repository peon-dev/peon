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
        $filesystemReader = $this->createStub(FilesystemReader::class);
        $filesystemReader->method('fileExists')->willReturn(true);

        $workingDirectory = '';

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

        $filesystemReader = $this->createStub(FilesystemReader::class);
        $filesystemReader->method('fileExists')->willReturn(false);

        $workingDirectory = '';

        $rector = new Rector($rectorBinary, $filesystemReader);
        $rector->runInDirectory($workingDirectory);
    }
}
