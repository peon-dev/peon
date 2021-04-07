<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\FileSystem;

use Nette\Utils\FileSystem;
use PHPMate\Domain\FileSystem\WorkingDirectory;

final class TemporaryLocalFileSystemWorkingDirectory implements WorkingDirectory
{
    public function __construct(
        private string $directory
    ) {
        $this->registerShutdown();
    }


    public function getAbsolutePath(): string
    {
        return $this->directory;
    }


    public function fileExists(string $file): bool
    {
        return file_exists($this->directory . '/' . $file);
    }


    private function registerShutdown(): void
    {
        register_shutdown_function(function() {
            FileSystem::delete($this->directory);
        });
    }
}
