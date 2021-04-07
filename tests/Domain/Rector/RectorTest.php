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
    public function testRunInWorkingDirectory(): void
    {
        $workingDirectory = $this->getWorkingDirectory(true);

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('execInWorkingDirectory')
            ->with(
                $workingDirectory,
                'process'
            );

        $rector = new Rector($rectorBinary);
        $rector->runInWorkingDirectory($workingDirectory);
    }


    public function testRunInWorkingDirectoryWillThrowExceptionWhenConfigMissing(): void
    {
        $this->expectException(RectorConfigFileMissing::class);

        $rectorBinary = $this->createStub(RectorBinary::class);
        $workingDirectory = $this->getWorkingDirectory(false);

        $rector = new Rector($rectorBinary);
        $rector->runInWorkingDirectory($workingDirectory);
    }


    private function getWorkingDirectory(bool $rectorConfigFileExists): WorkingDirectory
    {
        $workingDirectory = $this->createMock(WorkingDirectory::class);
        $workingDirectory->method('fileExists')
            ->with('rector.php')
            ->willReturn($rectorConfigFileExists);

        return $workingDirectory;
    }
}
