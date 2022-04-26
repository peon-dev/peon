<?php

declare(strict_types=1);

namespace Peon\Domain\Application;

use Peon\Domain\Application\Value\ApplicationLanguage;
use Peon\Domain\Application\Value\WorkingDirectory;

interface DetectApplicationLanguage
{
    public function inDirectory(WorkingDirectory $directory): ApplicationLanguage;
}
