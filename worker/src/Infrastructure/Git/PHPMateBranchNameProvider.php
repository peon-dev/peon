<?php

declare(strict_types=1);

namespace PHPMate\Worker\Infrastructure\Git;

use PHPMate\Worker\Domain\Git\BranchNameProvider;

final class PHPMateBranchNameProvider implements BranchNameProvider
{
    public function provideForProcedure(string $procedure): string
    {
        return 'phpmate/' . $procedure;
    }
}
