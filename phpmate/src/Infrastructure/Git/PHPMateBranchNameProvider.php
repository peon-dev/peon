<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Git;

use Nette\Utils\Strings;
use PHPMate\Domain\Tools\Git\BranchNameProvider;

final class PHPMateBranchNameProvider implements BranchNameProvider
{
    public function provideForTask(string $taskName): string
    {
        return 'phpmate/' . Strings::webalize($taskName);
    }
}
