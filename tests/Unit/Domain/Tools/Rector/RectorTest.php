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
     * @dataProvider provideTestGetProcessCommand
     */
    public function testGetProcessCommand(RectorProcessCommandConfiguration $commandConfiguration, string $expectedCommand): void
    {
        $rector = new Rector();

        $command = $rector->getProcessCommand($commandConfiguration);

        self::assertSame($expectedCommand, $command);
    }


    /**
     * @return \Generator<array{RectorProcessCommandConfiguration, string}>
     */
    public function provideTestGetProcessCommand(): \Generator
    {
        yield [
            new RectorProcessCommandConfiguration(),
            realpath(Rector::BINARY_EXECUTABLE) . ' process',
        ];

        yield [
            new RectorProcessCommandConfiguration(autoloadFile: 'autoload.php'),
            realpath(Rector::BINARY_EXECUTABLE) . ' process --autoload-file=autoload.php',
        ];

        yield [
            new RectorProcessCommandConfiguration(workingDirectory: 'directory'),
            realpath(Rector::BINARY_EXECUTABLE) . ' process --working-dir=directory',
        ];

        yield [
            new RectorProcessCommandConfiguration(config: 'config.php'),
            realpath(Rector::BINARY_EXECUTABLE) . ' process --config=config.php',
        ];

        yield [
            new RectorProcessCommandConfiguration(paths: ['src', 'app']),
            realpath(Rector::BINARY_EXECUTABLE) . ' process src app',
        ];

        yield [
            new RectorProcessCommandConfiguration('autoload.php', 'directory', 'project/config.php', ['src', 'app']),
            realpath(Rector::BINARY_EXECUTABLE) . ' process --autoload-file=autoload.php --working-dir=directory --config=project/config.php src app',
        ];
    }
}
