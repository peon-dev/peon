<?php

declare(strict_types=1);

namespace Peon\Infrastructure\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Peon\Domain\PhpApplication\ApplicationDirectoryProvider;

final class TemporaryLocalFileSystemApplicationDirectoryProvider implements ApplicationDirectoryProvider
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
