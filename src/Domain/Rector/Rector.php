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
        // TODO write test for configurations
        // TODO add arguments from $configuration to command
        $this->rectorBinary->executeCommand($directory, 'process');
    }
}
