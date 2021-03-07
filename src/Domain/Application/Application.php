<?php
declare (strict_types=1);

namespace Acme\Domain\Application;

use Acme\Domain\Composer\Composer;
use Acme\Domain\Rector\Rector;

final class Application
{
    public static function createFromDirectory(string $directory): Application
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
