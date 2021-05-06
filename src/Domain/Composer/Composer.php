<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

use PHPMate\Domain\Process\ProcessLogger;

final class Composer
{
    public function __construct(
        private ComposerBinary $composerBinary,
        private ProcessLogger $processLogger
    ) {}


    /**
     * @throws ComposerCommandFailed
     */
    public function install(string $directory, ComposerEnvironment $environment): void
    {
        $environmentVariables = [];

        if ($environment->auth) {
            $environmentVariables[ComposerEnvironment::AUTH] = $environment->auth;
        }

        // TODO: remove --ignore-platform-reqs once we have supported environment for the project
        $result = $this->composerBinary->executeCommand($directory,'install --ignore-platform-reqs --no-interaction', $environmentVariables);

        $this->processLogger->logResult($result);
    }
}
