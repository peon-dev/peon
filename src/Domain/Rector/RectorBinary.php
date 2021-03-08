<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use League\Flysystem\FilesystemReader;

interface RectorBinary
{
    public function exec(FilesystemReader $workingDirectory, string $command): void;
}
