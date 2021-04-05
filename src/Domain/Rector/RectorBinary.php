<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

interface RectorBinary
{
    public function execInDirectory(string $workingDirectory, string $command): void;
}
