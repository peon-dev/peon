<?php

declare(strict_types=1);

namespace Peon\Domain\Process;

interface SanitizeProcessOutput
{
    public function hideDetailedPaths(string $output): string;
}
