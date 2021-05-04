<?php

declare(strict_types=1);

namespace PHPMate\App;

use PHPMate\Domain\Job\Job;
use PHPMate\UseCase\RunRectorOnGitlabRepository;
use PHPMate\UseCase\RunRectorOnGitlabRepositoryUseCase;

// TODO: this could be middleware :-)
/*
 * Motivation:
 * - Run the use case
 * - Collect results
 * - Persist job
 */
class RunRectorOnGitlabRepositoryLauncher
{
    public function __construct(
        private RunRectorOnGitlabRepositoryUseCase $useCase
    ) {}


    public function launch(RunRectorOnGitlabRepository $command): void
    {
        // $now = new \DateTimeImmutable(); // TODO: clock

        // $job = new Job($now->getTimestamp());

        try {
            $this->useCase->__invoke($command);
            // job -> success
        } catch (\Throwable $exception) {
            // job -> fail
            throw $exception;
        }
    }
}
