<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

use League\Flysystem\FilesystemReader;

interface Rector
{
    public function runInDirectory(FilesystemReader $workingDirectory): void;
}
