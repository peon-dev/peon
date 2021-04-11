<?php

declare(strict_types=1);

namespace PHPMate\Domain\Composer;

interface ComposerBinary
{
    public function executeCommand(string $directory, string $command): void;
}
