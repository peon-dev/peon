<?php

declare(strict_types=1);

namespace Peon\Infrastructure\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Peon\Domain\Application\Value\WorkingDirectory;
use Peon\Domain\Application\ProvideApplicationDirectory;

final class TemporaryLocalFileSystemProvideApplicationDirectory implements ProvideApplicationDirectory
{
    public function __construct(
        private string $peonWorkingDirectoriesPath,
        private string $hostWorkingDirectoriesPath,
    ) {}


    public function provide(): WorkingDirectory
    {
        $randomDirectoryName = Random::generate();

        $localDirectory = $this->peonWorkingDirectoriesPath . '/' . $randomDirectoryName;
        $hostDirectory = $this->hostWorkingDirectoriesPath . '/' . $randomDirectoryName;

        FileSystem::createDir($localDirectory);
        print_r(shell_exec('ls -la ' . $localDirectory));

        $this->registerShutdown($localDirectory);

        return new WorkingDirectory($localDirectory, $hostDirectory);
    }


    private function registerShutdown(string $directory): void
    {
        register_shutdown_function(static function() use ($directory) {
            FileSystem::delete($directory);
        });
    }
}
