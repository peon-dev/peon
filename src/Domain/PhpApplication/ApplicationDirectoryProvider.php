<?php

declare(strict_types=1);

namespace PHPMate\Domain\PhpApplication;

interface ApplicationDirectoryProvider
{
    public function provide(): string;
}
