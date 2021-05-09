<?php

declare(strict_types=1);

namespace PHPMate\Worker\App;

use Lcobucci\Clock\Clock;
use PHPMate\Worker\Domain\Job\Job;
use PHPMate\Worker\Domain\Job\JobRepository;
use PHPMate\Worker\Domain\Process\ProcessLogger;
use PHPMate\Worker\UseCase\RunRectorOnGitlabRepository;
use PHPMate\Worker\UseCase\RunRectorOnGitlabRepositoryUseCase;

// TODO: this could be middleware :-)
class RunRectorOnGitlabRepositoryLauncher
{
    public function __construct(
        private RunRectorOnGitlabRepositoryUseCase $useCase,
        private JobRepository $jobRepository,
        private ProcessLogger $processLogger,
        private Clock $clock
    ) {}


    public function launch(RunRectorOnGitlabRepository $command): void
    {
        $now = $this->clock->now();
        $job = new Job($now->getTimestamp());
        $this->jobRepository->save($job);
        $startTime = microtime(true);

        try {
            $this->useCase->__invoke($command);
            $job->markAsSucceeded($this->getExecutionTime($startTime));
        } catch (\Throwable $exception) {
            $job->markAsFailed($this->getExecutionTime($startTime));

            throw $exception;
        } finally {
            foreach ($this->processLogger->getLogs() as $processResult) {
                $job->addLog($processResult);
            }

            $this->jobRepository->save($job);
        }
    }


    private function getExecutionTime(float $startTime): float
    {
        $finishTime = microtime(true);

        return $finishTime - $startTime;
    }
}
