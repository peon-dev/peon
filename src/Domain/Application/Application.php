<?php
declare (strict_types=1);

namespace Acme\Domain\Application;

use Acme\Domain\Composer\Composer;
use Acme\Domain\Rector\Rector;

interface Application
{
    public static function createFromDirectory(string $directory);
    public function installComposer(Composer $composer);
    public function runRector(Rector $rector);
}
