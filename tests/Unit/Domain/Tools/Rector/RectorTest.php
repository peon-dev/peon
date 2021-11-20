<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Tools\Rector;

use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Process\Value\ProcessResult;
use PHPMate\Domain\Tools\Rector\Rector;
use PHPMate\Domain\Tools\Rector\RectorBinary;
use PHPMate\Domain\Tools\Rector\Exception\RectorCommandFailed;
use PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;
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
    public function testProcess(\PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration $commandConfiguration, string $expectedCommand): void
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
        $this->expectException(\PHPMate\Domain\Tools\Rector\Exception\RectorCommandFailed::class);

        $projectDirectory = '/';

        $rectorBinary = $this->createMock(RectorBinary::class);
        $rectorBinary->expects(self::once())
            ->method('executeCommand')
            ->willThrowException(new \PHPMate\Domain\Tools\Rector\Exception\RectorCommandFailed());

        $rector = new Rector($rectorBinary, $this->processLogger);
        $rector->process($projectDirectory, new \PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration());
    }


    /**
     * @return \Generator<array{\PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration, string}>
     */
    public function provideTestProcessData(): \Generator
    {
        yield [
            new \PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration(),
            'process',
        ];

        yield [
            new RectorProcessCommandConfiguration(autoloadFile: 'autoload.php'),
            'process --autoload-file autoload.php',
        ];

        yield [
            new \PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration(workingDirectory: 'directory'),
            'process --working-dir directory',
        ];

        yield [
            new RectorProcessCommandConfiguration(config: 'config.php'),
            'process --config config.php',
        ];

        yield [
            new \PHPMate\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration('autoload.php', 'directory', 'project/config.php'),
            'process --autoload-file autoload.php --working-dir directory --config project/config.php',
        ];
    }
}
