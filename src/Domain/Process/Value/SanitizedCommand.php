<?php

declare(strict_types=1);

namespace Peon\Domain\Process\Value;

use Peon\Domain\Process\SanitizeProcessCommand;

final class SanitizedCommand
{
    public function __construct(string $command, SanitizeProcessCommand $sanitizeProcessCommand)
    {
    }
}
