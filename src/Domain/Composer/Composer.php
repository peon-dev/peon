<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

use League\Flysystem\FilesystemReader;

final class Composer
{
    public function __construct(
        private ComposerBinary $composerBinary,
        private FilesystemReader $filesystemReader,
    ) {}


    /**
     * @throws ComposerJsonFileMissing
     */
    public function installInDirectory(string $workingDirectory): void
    {
        if ($this->filesystemReader->fileExists($workingDirectory . '/composer.json') === false) {
            throw new ComposerJsonFileMissing();
        }

        $this->composerBinary->execInDirectory($workingDirectory,'install');
    }
}
