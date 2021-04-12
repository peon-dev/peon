<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Rector;

use PHPMate\Domain\Rector\Rector;
use PHPMate\Domain\Rector\RectorBinary;
use PHPMate\Domain\Rector\RectorProcessCommandConfiguration;
use PHPUnit\Framework\TestCase;

class RectorTest extends TestCase
{
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

        $rector = new Rector($rectorBinary);
        $rector->process($projectDirectory, $commandConfiguration);
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
