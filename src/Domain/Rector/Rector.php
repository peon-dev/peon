<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use PHPMate\Domain\FileSystem\WorkingDirectory;

// TODO: once we have buildpacks, need to run in docker image with volume
final class Rector
{
    public function __construct(
        private RectorBinary $rectorBinary,
    ) {}

    /**
     * @throws RectorConfigFileMissing
     */
    public function runInWorkingDirectory(WorkingDirectory $workingDirectory): void
    {
        if ($workingDirectory->fileExists('rector.php') === false) {
            throw new RectorConfigFileMissing();
        }

        $this->rectorBinary->execInWorkingDirectory($workingDirectory, 'process');
    }
}
