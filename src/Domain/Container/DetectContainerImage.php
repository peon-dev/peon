<?php

declare(strict_types=1);

namespace Peon\Domain\Container;

use Peon\Domain\Application\Value\ApplicationLanguage;

interface DetectContainerImage
{
    public function forLanguage(ApplicationLanguage $language): string;
}
