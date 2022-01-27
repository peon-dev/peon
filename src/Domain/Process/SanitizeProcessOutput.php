<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

final class SanitizeProcessOutput
{
    public function hideDetailedPaths(string $output): string
    {
        return $output;
    }
}
