<?php
declare (strict_types=1);

namespace PHPMate\Domain\Application;

use PHPMate\Domain\Composer\Composer;
use PHPMate\Domain\Rector\Rector;

final class Application
{
    public static function fromDirectory(string $directory): Application
    {
        return new self();
    }

    public function installComposer(Composer $composer): void
    {
        // TODO: not implemented
    }

    public function runRector(Rector $rector): void
    {
        // TODO: not implemented
    }
}
