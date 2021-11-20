<?php
declare(strict_types=1);

namespace PHPMate\Tests\Unit\Domain\Tools\Composer;

use PHPMate\Domain\Tools\Composer\Composer;
use PHPMate\Domain\Tools\Composer\ComposerBinary;
use PHPMate\Domain\Tools\Composer\Value\ComposerEnvironment;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Process\Value\ProcessResult;
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
            new \PHPMate\Domain\Tools\Composer\Value\ComposerEnvironment(),
            [],
        ];

        yield [
            new \PHPMate\Domain\Tools\Composer\Value\ComposerEnvironment('{}'),
            [\PHPMate\Domain\Tools\Composer\Value\ComposerEnvironment::AUTH => '{}'],
        ];
    }


    // TODO: cover logging by tests
}
