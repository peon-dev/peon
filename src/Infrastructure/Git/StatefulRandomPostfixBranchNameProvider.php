<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Git;

use Nette\Utils\Random;
use PHPMate\Domain\Tools\Git\BranchNameProvider;

final class StatefulRandomPostfixBranchNameProvider implements BranchNameProvider
{
    private string $branchName = '';


    public function __construct(
        private PHPMateBranchNameProvider $originalBranchNameProvider
    ) {}


    public function provideForTask(string $taskName): string
    {
        if ($this->branchName === '') {
            $this->branchName = sprintf(
                '%s-%s',
                $this->originalBranchNameProvider->provideForTask($taskName),
                Random::generate()
            );
        }

        return $this->branchName;
    }
}
