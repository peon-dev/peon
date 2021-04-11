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
    public function testProcess(): void
    {
        $projectDirectory = $this->getWorkingDirectory(true);

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                $projectDirectory,
                'process'
            );

        $rector = new Rector($rectorBinary);
        $rector->process($projectDirectory);
    }


    public function testProcessWillThrowExceptionWhenConfigMissing(): void
    {
        $this->expectException(RectorConfigFileMissing::class);

        $rectorBinary = $this->createStub(RectorBinary::class);
        $projectDirectory = $this->getWorkingDirectory(false);

        $rector = new Rector($rectorBinary);
        $rector->process($projectDirectory);
    }


    private function getWorkingDirectory(bool $rectorConfigFileExists): WorkingDirectory
    {
        $projectDirectory = $this->createMock(WorkingDirectory::class);
        $projectDirectory->method('fileExists')
            ->with('rector.php')
            ->willReturn($rectorConfigFileExists);

        return $projectDirectory;
    }
}
