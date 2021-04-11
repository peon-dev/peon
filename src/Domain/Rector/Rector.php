<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use PHPMate\Domain\FileSystem\WorkingDirectory;

final class Rector
{
    public function __construct(
        private RectorBinary $rectorBinary,
    ) {}

    /**
     * @param array<RectorProcessCommandConfiguration> $configurations
     */
    public function process(WorkingDirectory $projectDirectory, array $configurations = []): void
    // TODO write test for configurations
    {
        if ($configurations === []) {
            $configurations = [new RectorProcessCommandConfiguration()];
        }

        foreach ($configurations as $configuration) {
            // TODO add arguments from $configuration to command

            $this->rectorBinary->executeCommand($projectDirectory, 'process');
        }
    }
}
