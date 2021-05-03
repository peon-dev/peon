<?php
declare(strict_types=1);

namespace PHPMate\Tests\Domain\Composer;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Composer\ComposerBinary;
use PHPMate\Domain\Composer\ComposerEnvironment;
use PHPMate\Infrastructure\Dummy\DummyProcessLogger;
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

        $composerBinary = $this->createMock(ComposerBinary::class);
        $composerBinary->expects(self::once())
            ->method('executeCommand')
            ->with(
                $projectDirectory,
                'install --ignore-platform-reqs --no-scripts --no-interaction',
                $expectedEnvironmentVariables
            );

        $composer = new Composer($composerBinary, new DummyProcessLogger());
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
