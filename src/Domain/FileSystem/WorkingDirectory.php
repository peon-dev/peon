<?php

declare(strict_types=1);

namespace PHPMate\Domain\FileSystem;

interface WorkingDirectory
{
    public function getPath(): string;

    public function fileExists(string $file): bool;
}
