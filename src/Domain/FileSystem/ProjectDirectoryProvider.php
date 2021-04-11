<?php

declare(strict_types=1);

namespace PHPMate\Domain\FileSystem;

interface ProjectDirectoryProvider
{
    public function provide(): string;
}
