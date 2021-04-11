<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

use PHPMate\Domain\FileSystem\WorkingDirectory;

final class Composer
{
    public function __construct(
        private ComposerBinary $composerBinary,
    ) {}


    /**
     * @throws ComposerJsonFileMissing
     */
    public function install(WorkingDirectory $projectDirectory): void
    {
        if ($projectDirectory->fileExists('composer.json') === false) {
            throw new ComposerJsonFileMissing();
        }

        // TODO: remove --ignore-platform-reqs once we have supported environment for the project
        $this->composerBinary->executeCommand($projectDirectory,'install --ignore-platform-reqs --no-scripts --no-interaction');
    }
}
