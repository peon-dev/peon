<?php

declare(strict_types=1);

namespace PHPMate\Domain\FileSystem;

use League\Flysystem\FilesystemWriter;
use Nette\Utils\Random;

final class WorkingDirectoryProvider
{
    public function __construct(
        private string $baseDir,
        private FilesystemWriter $filesystemWriter
    ) {}


    public function provide(): string
    {
        $directory = Random::generate();

        $this->filesystemWriter->createDirectory($directory);

        $this->registerShutdown($directory);

        return $this->baseDir . '/' . $directory;
    }


    private function registerShutdown(string $directory): void
    {
        register_shutdown_function(function() use ($directory) {
            $this->filesystemWriter->deleteDirectory($directory);
        });
    }
}
