<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Git;

use Nette\Utils\Strings;
use Peon\Domain\Tools\Git\ProvideBranchName;

final class PeonProvideBranchName implements ProvideBranchName
{
    public function forTask(string $taskName): string
    {
        return 'peon/' . Strings::webalize($taskName);
    }
}
