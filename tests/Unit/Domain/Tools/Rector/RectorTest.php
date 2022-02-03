<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Tools\Rector;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Process\Exception\ProcessFailed;
use Peon\Domain\Process\ExecuteCommand;
use Peon\Domain\Process\Value\ProcessResult;
use Peon\Domain\Tools\Rector\Rector;
use Peon\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;
use PHPUnit\Framework\TestCase;

class RectorTest extends TestCase
{
    /**
     * @dataProvider provideTestProcessData
     */
    public function testProcess(RectorProcessCommandConfiguration $commandConfiguration, string $expectedCommand): void
    {
        $jobId = new JobId('');
        $projectDirectory = '/';

        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->with(
                $jobId,
                $projectDirectory,
                $expectedCommand,
                3600
            );

        $rector = new Rector($executeCommand);
        $rector->process($jobId, $projectDirectory, $commandConfiguration);
    }


    public function testProcessThrowsExceptionOnNonZeroExitCode(): void
    {
        $this->expectException(ProcessFailed::class);

        $jobId = new JobId('');
        $projectDirectory = '/';

        $executeCommand = $this->createMock(ExecuteCommand::class);
        $executeCommand->expects(self::once())
            ->method('inDirectory')
            ->willThrowException(new ProcessFailed(new ProcessResult(1, 0, '')));

        $rector = new Rector($executeCommand);
        $rector->process($jobId, $projectDirectory, new RectorProcessCommandConfiguration());
    }


    /**
     * @return \Generator<array{RectorProcessCommandConfiguration, string}>
     */
    public function provideTestProcessData(): \Generator
    {
        yield [
            new RectorProcessCommandConfiguration(),
            Rector::BINARY_EXECUTABLE . ' process',
        ];

        yield [
            new RectorProcessCommandConfiguration(autoloadFile: 'autoload.php'),
            Rector::BINARY_EXECUTABLE . ' process --autoload-file=autoload.php',
        ];

        yield [
            new RectorProcessCommandConfiguration(workingDirectory: 'directory'),
            Rector::BINARY_EXECUTABLE . ' process --working-dir=directory',
        ];

        yield [
            new RectorProcessCommandConfiguration(config: 'config.php'),
            Rector::BINARY_EXECUTABLE . ' process --config=config.php',
        ];

        yield [
            new RectorProcessCommandConfiguration(paths: ['src', 'app']),
            Rector::BINARY_EXECUTABLE . ' process src app',
        ];

        yield [
            new RectorProcessCommandConfiguration('autoload.php', 'directory', 'project/config.php', ['src', 'app']),
            Rector::BINARY_EXECUTABLE . ' process --autoload-file=autoload.php --working-dir=directory --config=project/config.php src app',
        ];
    }
}
