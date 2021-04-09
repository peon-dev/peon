<?php

declare(strict_types=1);

namespace PHPMate\Domain\FileSystem;

final class WorkingDirectory
{
    public function __construct(
        private string $directory
    ) {}


    public function getAbsolutePath(): string
    {
        return $this->directory;
    }


    public function fileExists(string $file): bool
    {
        return file_exists($this->directory . '/' . $file);
    }
}
