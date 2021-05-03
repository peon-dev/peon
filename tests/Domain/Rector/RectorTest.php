<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Rector;

use PHPMate\Domain\Process\ProcessResult;
use PHPMate\Domain\Rector\Rector;
use PHPMate\Domain\Rector\RectorBinary;
use PHPMate\Domain\Rector\RectorCommandFailed;
use PHPMate\Domain\Rector\RectorProcessCommandConfiguration;
use PHPMate\Infrastructure\Dummy\DummyProcessLogger;
use PHPUnit\Framework\TestCase;

class RectorTest extends TestCase
{
    private DummyProcessLogger $processLogger;


    protected function setUp(): void
    {
        parent::setUp();

        $this->processLogger = new DummyProcessLogger();
    }


    /**
     * @dataProvider provideTestProcessData
     */
    public function testProcess(RectorProcessCommandConfiguration $commandConfiguration, string $expectedCommand): void
    {
        $projectDirectory = '/';

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                $projectDirectory,
                $expectedCommand
            );

        $rector = new Rector($rectorBinary, $this->processLogger);
        $rector->process($projectDirectory, $commandConfiguration);
    }


    public function testProcessThrowsExceptionOnNonZeroExitCode(): void
    {
        $this->expectException(RectorCommandFailed::class);
        $this->expectExceptionMessage('Message');

        $projectDirectory = '/';

        $processResult = $this->createStub(ProcessResult::class);
        $processResult->method('getExitCode')->willReturn(1);
        $processResult->method('getOutput')->willReturn('Message');

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
