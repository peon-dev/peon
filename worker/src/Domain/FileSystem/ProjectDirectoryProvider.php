<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\FileSystem;

interface ProjectDirectoryProvider
{
    public function provide(): string;
}
