<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use PHPMate\Domain\FileSystem\WorkingDirectory;
use PHPMate\Domain\FileSystem\WorkingDirectoryProvider;

final class TemporaryLocalFileSystemWorkingDirectoryProvider implements WorkingDirectoryProvider
{
    public function __construct(
        private string $baseDir
    ) {}


    public function provide(): WorkingDirectory
    {
        $directory = $this->baseDir . '/' . Random::generate();

        FileSystem::createDir($directory);

        return new TemporaryLocalFileSystemWorkingDirectory($directory);
    }
}
