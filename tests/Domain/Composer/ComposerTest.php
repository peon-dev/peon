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
    public function testInstallInWorkingDirectory(): void
    {
        $projectDirectory = '/';

        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                $projectDirectory,
                'install --ignore-platform-reqs --no-scripts --no-interaction'
            );

        $composer = new Composer($composerBinary);
        $composer->install($projectDirectory);
    }
}
