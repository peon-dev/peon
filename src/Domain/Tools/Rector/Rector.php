<?php

declare(strict_types=1);

namespace Peon\Domain\Tools\Rector;

use Peon\Domain\Process\ProcessLogger;
use Peon\Domain\Tools\Rector\Exception\RectorCommandFailed;
use Peon\Domain\Tools\Rector\Value\RectorProcessCommandConfiguration;

class Rector
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
            $command .= ' --autoload-file=' . $configuration->autoloadFile;
        }

        if ($configuration->workingDirectory) {
            $command .= ' --working-dir=' . $configuration->workingDirectory;
        }

        if ($configuration->config !== null) {
            $command .= ' --config=' . $configuration->config;
        }

        if ($configuration->paths) {
            $command .= ' ' . implode(' ', $configuration->paths);
        }

        $result = $this->rectorBinary->executeCommand($directory, $command);

        $this->processLogger->logResult($result);
    }
}
