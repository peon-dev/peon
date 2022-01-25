<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication;

interface ProvideApplicationDirectory
{
    public function provide(): string;
}
