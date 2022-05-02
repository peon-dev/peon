<?php

declare(strict_types=1);

namespace Peon\Domain\Application;

use Peon\Domain\Application\Value\ApplicationLanguage;
use Peon\Domain\Application\Value\WorkingDirectory;

class DetectApplicationLanguage
{
    public function inDirectory(WorkingDirectory $directory): ApplicationLanguage
    {
        return new ApplicationLanguage('PHP', '8.1');
    }
}
