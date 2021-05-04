<?php

declare(strict_types=1);

namespace PHPMate\App;

use PHPMate\UseCase\RunRectorOnGitlabRepository;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;

// TODO: this could be middleware :-)
class RunRectorOnGitlabRepositoryLauncher
{
    public function __construct(
        private RunRectorOnGitlabRepositoryUseCase $useCase
    ) {}


    public function launch(RunRectorOnGitlabRepository $command): void
    {
       $this->useCase->__invoke($command);
    }
}
