<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

interface SanitizeProcessCommand
{
    public function maskCredentials(string $command): string;
}
