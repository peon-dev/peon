<?php

declare(strict_types=1);

namespace PHPMate\Domain\FileSystem;

interface WorkingDirectoryProvider
{
    public function provide(): WorkingDirectory;
}
