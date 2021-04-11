<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

final class Composer
{
    public function __construct(
        private ComposerBinary $composerBinary,
    ) {}


    public function install(string $directory): void
    {
        // TODO: remove --ignore-platform-reqs once we have supported environment for the project
        $this->composerBinary->executeCommand($directory,'install --ignore-platform-reqs --no-scripts --no-interaction');
    }
}
