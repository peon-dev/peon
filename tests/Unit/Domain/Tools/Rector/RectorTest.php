<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Tools\Rector;

use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Tools\Rector\Rector;
use PHPMate\Domain\Tools\Rector\RectorBinary;
use PHPMate\Domain\Tools\Rector\RectorCommandFailed;
use PHPMate\Domain\Tools\Rector\RectorProcessCommandConfiguration;
use PHPUnit\Framework\TestCase;

class RectorTest extends TestCase
{
    private ProcessLogger $processLogger;


    protected function setUp(): void
    {
        parent::setUp();

        $this->processLogger = new ProcessLogger();
    }


    /**
     * @dataProvider provideTestProcessData
     */
    public function testProcess(RectorProcessCommandConfiguration $commandConfiguration, string $expectedCommand): void
    {
        $projectDirectory = '/';
        $dummyProcessResult = new ProcessResult('', 0, '', 0);

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                $projectDirectory,
                $expectedCommand
            )
            ->willReturn($dummyProcessResult);

        $rector = new Rector($rectorBinary, $this->processLogger);
        $rector->process($projectDirectory, $commandConfiguration);
    }


    public function testProcessThrowsExceptionOnNonZeroExitCode(): void
    {
        $this->expectException(RectorCommandFailed::class);
        $this->expectExceptionMessage('Message');

        $projectDirectory = '/';
        $processResult = new ProcessResult('', 1, 'Message', 0);

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('executeCommand')
            ->willReturn($processResult);

        $rector = new Rector($rectorBinary, $this->processLogger);
        $rector->process($projectDirectory, new RectorProcessCommandConfiguration());
    }


    /**
     * @return \Generator<array{RectorProcessCommandConfiguration, string}>
     */
    public function provideTestProcessData(): \Generator
    {
        yield [
            new RectorProcessCommandConfiguration(),
            'process',
        ];

        yield [
            new RectorProcessCommandConfiguration(autoloadFile: 'autoload.php'),
            'process --autoload-file autoload.php',
        ];

        yield [
            new RectorProcessCommandConfiguration(workingDirectory: 'directory'),
            'process --working-dir directory',
        ];

        yield [
            new RectorProcessCommandConfiguration(config: 'config.php'),
            'process --config config.php',
        ];

        yield [
            new RectorProcessCommandConfiguration('autoload.php', 'directory', 'project/config.php'),
            'process --autoload-file autoload.php --working-dir directory --config project/config.php',
        ];
    }
}
