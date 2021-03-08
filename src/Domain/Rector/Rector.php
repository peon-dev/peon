<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use League\Flysystem\FilesystemReader;

final class Rector
{
    public function __construct(
        private RectorBinary $rectorBinary
    ) {}

    /**
     * @throws RectorConfigFileMissing
     */
    public function runInDirectory(FilesystemReader $workingDirectory): void
    {
        $this->rectorBinary->exec($workingDirectory, 'process --dry-run');
    }
}
