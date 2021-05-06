<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Git;

use Nette\Utils\Random;
use PHPMate\Domain\Git\BranchNameProvider;

final class StatefulRandomPostfixBranchNameProvider implements BranchNameProvider
{
    private string $branchName = '';


    public function __construct(
        private PHPMateBranchNameProvider $originalBranchNameProvider
    ) {}


    public function provideForProcedure(string $procedure): string
    {
        if ($this->branchName === '') {
            $this->branchName = sprintf(
                '%s-%s',
                $this->originalBranchNameProvider->provideForProcedure($procedure),
                Random::generate()
            );
        }

        return $this->branchName;
    }
}
