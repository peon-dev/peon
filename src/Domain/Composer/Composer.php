<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

final class Composer
{
    /**
     * @throws ComposerJsonFileMissing
     */
    public function installInDirectory(string $directory): void
    {
        // TODO: abstract filesystem
        if (is_file($directory . '/composer.json') === false) {
            throw new ComposerJsonFileMissing();
        }

        // $this->composerBinary->exec('install');
    }
}
