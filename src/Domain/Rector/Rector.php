<?php

declare(strict_types=1);

namespace PHPMate\Domain\Rector;

interface Rector
{
    public function runInDirectory(string $directory): void;
}
