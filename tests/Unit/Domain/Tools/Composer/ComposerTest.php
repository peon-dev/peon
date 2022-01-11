<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\Domain\Tools\Composer;

use Peon\Domain\Tools\Composer\Composer;
use Peon\Domain\Tools\Composer\ComposerBinary;
use Peon\Domain\Tools\Composer\Value\ComposerEnvironment;
use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Process\Value\ProcessResult;
use PHPUnit\Framework\TestCase;

class ComposerTest extends TestCase
{
    /**
     * @param array<string, string> $expectedEnvironmentVariables
     *
     * @dataProvider provideTestInstallData
     */
    public function testInstall(ComposerEnvironment $environment, array $expectedEnvironmentVariables): void
    {
        $projectDirectory = '/';

        $emptyProcessResult = new ProcessResult('', 0, '', 0);
        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                $projectDirectory,
                'install --ignore-platform-reqs --no-interaction',
                $expectedEnvironmentVariables
            )
            ->willReturn($emptyProcessResult);

        $composer = new Composer($composerBinary, new ProcessLogger());
        $composer->install($projectDirectory, $environment);
    }


    /**
     * @return \Generator<array{ComposerEnvironment|null, array<string, string>}>
     */
    public function provideTestInstallData(): \Generator
    {
        yield [
            new ComposerEnvironment(),
            [],
        ];

        yield [
            new ComposerEnvironment('{}'),
            [ComposerEnvironment::AUTH => '{}'],
        ];
    }


    public function testGetPsr4Roots(): void
    {
    }


    // TODO: cover logging by tests
}
