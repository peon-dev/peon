<?php

declare(strict_types=1);

namespace PHPMate\Domain\FileSystem;

interface WorkingDirectory
{
    public function getAbsolutePath(): string;

    public function fileExists(): bool;
}