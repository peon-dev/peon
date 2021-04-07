<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use League\Flysystem\FilesystemReader;

final class Rector
{
    public function __construct(
        private RectorBinary $rectorBinary,
        private FilesystemReader $filesystemReader
    ) {}

    /**
     * @throws RectorConfigFileMissing
     */
    public function runInWorkingDirectory(string $workingDirectory): void
    {
        if ($this->filesystemReader->fileExists($workingDirectory . '/rector.php') === false) {
            throw new RectorConfigFileMissing();
        }

        $this->rectorBinary->execInWorkingDirectory($workingDirectory, 'process');
    }
}
