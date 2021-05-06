<?php

declare(strict_types=1);

namespace PHPMate\Worker\Domain\Git;

interface BranchNameProvider
{
    public function provideForProcedure(string $procedure): string;
}
