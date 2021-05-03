<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Dummy;

use PHPMate\Domain\Logger\Logger;

final class DummyLogger implements Logger
{
    public function log(string $command, string $commandOutput): void
    {
        // TODO: Implement log() method.
    }
}
