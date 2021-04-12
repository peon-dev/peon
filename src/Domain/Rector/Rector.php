<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

final class Rector
{
    public function __construct(
        private RectorBinary $rectorBinary,
    ) {}

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

        $this->rectorBinary->executeCommand($directory, $command);
    }
}
