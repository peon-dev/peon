<?php

declare(strict_types=1);

namespace Peon\Domain\Container;

use Peon\Domain\Application\Value\ApplicationLanguage;

class DetectContainerImage
{
    public function forLanguage(ApplicationLanguage $language): string
    {
        return 'ghcr.io/peon-dev/peon:master';
    }
}
