<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Rector;

use PHPMate\Domain\FileSystem\WorkingDirectory;
use PHPMate\Domain\Rector\Rector;
use PHPMate\Domain\Rector\RectorBinary;
use PHPMate\Domain\Rector\RectorConfigFileMissing;
use PHPUnit\Framework\TestCase;

class RectorTest extends TestCase
{
    public function testRunInDirectory(): void
    {
        $workingDirectory = $this->createStub(WorkingDirectory::class);
        $workingDirectory->method('fileExists')->willReturn(true);

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('execInDirectory')
            ->with(
                $workingDirectory,
                'process --dry-run'
            );

        $rector = new Rector($rectorBinary);
        $rector->runInDirectory($workingDirectory);
    }

    public function testRunInDirectoryWillThrowExceptionWhenConfigMissing(): void
    {
        $this->expectException(RectorConfigFileMissing::class);

        $rectorBinary = $this->createStub(RectorBinary::class);

        $rector = new Rector($rectorBinary);

        $workingDirectory = $this->createMock(WorkingDirectory::class);
        $workingDirectory->method('fileExists')->willReturn(false);

        $rector->runInDirectory($workingDirectory);
    }
}
