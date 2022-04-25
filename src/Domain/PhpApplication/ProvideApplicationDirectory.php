<?php

declare(strict_types=1);

namespace Peon\Domain\PhpApplication;

use Peon\Domain\Application\Value\WorkingDirectory;

interface ProvideApplicationDirectory
{
    public function provide(): WorkingDirectory;
}
