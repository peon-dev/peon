<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Git;

use Nette\Utils\Random;
use PHPMate\Domain\Git\BranchNameProvider;

final class RandomPostfixBranchNameProvider implements BranchNameProvider
{
    public function __construct(
        private PHPMateBranchNameProvider $originalBranchNameProvider
    ) {}


    public function provideForProcedure(string $procedure): string
    {
        return $this->originalBranchNameProvider->provideForProcedure($procedure) . '-' . Random::generate();
    }
}
