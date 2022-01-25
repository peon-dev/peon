<?php

declare(strict_types=1);

namespace Peon\Domain\Process\Value;

use Peon\Domain\Process\SanitizeProcessCommand;

final class Command
{
    private function __construct(
        public readonly string $command
    ) {}


    public static function fromDirty(string $command, SanitizeProcessCommand $sanitizeProcessCommand): self
    {
        return new self(
            $sanitizeProcessCommand->maskCredentials($command)
        );
    }


    public static function fromSanitized(string $command): self
    {
        return new self($command);
    }
}
