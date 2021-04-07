<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Git;

use PHPMate\Domain\Git\BranchNameProvider;

final class PHPMateBranchNameProvider implements BranchNameProvider
{
    public function provideForProcedure(string $procedure): string
    {
        return 'phpmate/' . $procedure;
    }
}
