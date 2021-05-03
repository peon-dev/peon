<?php

declare(strict_types=1);

namespace PHPMate\Domain\Logger;

interface Logger
{
    public function log(string $command, string $commandOutput): void;
}
