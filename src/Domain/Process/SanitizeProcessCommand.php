<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

final class SanitizeProcessCommand
{
    public function maskCredentials(string $command): string
    {
        return $command;
    }
}
