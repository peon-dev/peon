<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Composer;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\Composer\ComposerEnvironment;
use PHPMate\Domain\Process\ProcessLogger;
use PHPMate\Domain\Process\ProcessResult;
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


    // TODO: cover logging by tests
}
