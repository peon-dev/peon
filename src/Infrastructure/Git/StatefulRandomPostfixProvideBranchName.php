<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Git;

use Nette\Utils\Random;
use Peon\Domain\Tools\Git\ProvideBranchName;

final class StatefulRandomPostfixProvideBranchName implements ProvideBranchName
{
    private string $branchName = '';


    public function __construct(
        private PeonProvideBranchName $originalBranchNameProvider
    ) {}


    public function forTask(string $taskName): string
    {
        if ($this->branchName === '') {
            $this->branchName = sprintf(
                '%s-%s',
                $this->originalBranchNameProvider->forTask($taskName),
                Random::generate()
            );
        }

        return $this->branchName;
    }


    public function resetState(): void
    {
        $this->branchName = '';
    }
}
