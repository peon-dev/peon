<?php

declare(strict_types=1);

namespace PHPMate\Domain\FileSystem;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class WorkingDirectory // TODO: final
{
    public function __construct(
        public string $path,
    ) {
    }

    public function fileExists(string $file): bool
    {
        // TODO: use filesystem reader
        return file_exists($this->path . '/' . $file);
    }
}
