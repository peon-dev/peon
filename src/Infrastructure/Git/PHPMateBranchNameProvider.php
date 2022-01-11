<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Git;

use Nette\Utils\Strings;
use Peon\Domain\Tools\Git\BranchNameProvider;

final class PeonBranchNameProvider implements BranchNameProvider
{
    public function provideForTask(string $taskName): string
    {
        return 'peon/' . Strings::webalize($taskName);
    }
}
