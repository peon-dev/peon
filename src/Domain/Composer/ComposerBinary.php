<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

interface ComposerBinary
{
    public function exec(string $command): void;
}
