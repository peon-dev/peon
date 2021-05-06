<?php

declare(strict_types=1);

namespace PHPMate\Worker\Infrastructure\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use PHPMate\Worker\Domain\FileSystem\ProjectDirectoryProvider;

final class TemporaryLocalFileSystemProjectDirectoryProvider implements ProjectDirectoryProvider
{
    public function __construct(
        private string $baseDir
    ) {}


    public function provide(): string
    {
        $directory = $this->baseDir . '/' . Random::generate();

        FileSystem::createDir($directory);

        $this->registerShutdown($directory);

        return $directory;
    }


    private function registerShutdown(string $directory): void
    {
        register_shutdown_function(static function() use ($directory) {
            FileSystem::delete($directory);
        });
    }
}
