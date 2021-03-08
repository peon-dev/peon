<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemReader;

final class Composer
{
    public function __construct(
        private ComposerBinary $composerBinary
    ) {}


    /**
     * @throws ComposerJsonFileMissing
     * @throws FilesystemException
     */
    public function installInDirectory(FilesystemReader $filesystem): void
    {
        if ($filesystem->fileExists('composer.json') === false) {
            throw new ComposerJsonFileMissing();
        }

        $this->composerBinary->exec('install');
    }
}
