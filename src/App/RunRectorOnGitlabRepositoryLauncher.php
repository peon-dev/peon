<?php

declare(strict_types=1);

namespace PHPMate\App;

use PHPMate\Domain\Job\Job;
use PHPMate\Domain\Job\JobRepository;
use PHPMate\Domain\Process\ProcessLogger;
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
        private RunRectorOnGitlabRepositoryUseCase $useCase,
        private JobRepository $jobRepository,
        private ProcessLogger $processLogger
    ) {}


    public function launch(RunRectorOnGitlabRepository $command): void
    {
        $now = new \DateTimeImmutable(); // TODO: clock

        $job = new Job($now->getTimestamp());
        $this->jobRepository->save($job);

        try {
            $this->useCase->__invoke($command);
            $job->markAsSucceeded();
        } catch (\Throwable $exception) {
            $job->markAsFailed();

            throw $exception;
        } finally {
            foreach ($this->processLogger->getLogs() as $processResult) {
                $job->addLog($processResult);
            }

            $this->jobRepository->save($job);
        }
    }
}
