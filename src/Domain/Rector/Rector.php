<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

final class Rector
{
    public function __construct(
        private RectorBinary $rectorBinary,
    ) {}

    /**
     * @param array<RectorProcessCommandConfiguration> $configurations
     */
    public function process(string $directory, array $configurations = []): void
    // TODO write test for configurations
    {
        if ($configurations === []) {
            $configurations = [new RectorProcessCommandConfiguration()];
        }

        foreach ($configurations as $configuration) {
            // TODO add arguments from $configuration to command

            $this->rectorBinary->executeCommand($directory, 'process');
        }
    }
}
