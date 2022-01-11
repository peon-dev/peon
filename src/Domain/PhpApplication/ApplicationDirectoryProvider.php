<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication;

interface ApplicationDirectoryProvider
{
    public function provide(): string;
}
