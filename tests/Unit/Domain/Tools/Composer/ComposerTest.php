<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Tools\Composer;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Tools\Composer\Composer;
use PHPUnit\Framework\TestCase;

class ComposerTest extends TestCase
{
    public function testInstall(): void
    {
        $projectDirectory = '/';
        $jobId = new JobId('');

        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with(
                $jobId,
                $projectDirectory,
                'composer install --ignore-platform-reqs --no-interaction',
                2 * 60
            );

        $composer = new Composer($executeCommand);
        $composer->install($jobId, $projectDirectory);
    }


    public function testGetPsr4Roots(): void
    {
        $this->markTestIncomplete();
    }
}
