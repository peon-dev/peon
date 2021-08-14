<?php

declare(strict_types=1);

namespace PHPMate\Domain\Tools\Rector;

use PHPMate\Domain\Process\ProcessLogger;

final class Rector
{
    public function __construct(
        private RectorBinary $rectorBinary,
        private ProcessLogger $processLogger
    ) {}

    /**
     * @throws RectorCommandFailed
     */
    public function process(string $directory, RectorProcessCommandConfiguration $configuration): void
    {
        $command = 'process';

        if ($configuration->autoloadFile !== null) {
            $command .= ' --autoload-file ' . $configuration->autoloadFile;
        }

        if ($configuration->workingDirectory) {
            $command .= ' --working-dir ' . $configuration->workingDirectory;
        }

        if ($configuration->config !== null) {
            $command .= ' --config ' . $configuration->config;
        }

        $result = $this->rectorBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);

        if ($result->exitCode !== 0) {
            throw new RectorCommandFailed($result->output);
        }
    }
}
