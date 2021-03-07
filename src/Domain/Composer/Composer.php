<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

interface Composer
{
    /**
     * @throws ComposerJsonFileMissing
     */
    public function installInDirectory(string $directory): void;
}
