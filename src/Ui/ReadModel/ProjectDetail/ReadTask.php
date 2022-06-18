<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\ProjectDetail;

use Cron\CronExpression;
use DateTimeImmutable;
use JetBrains\PhpStorm\Immutable;
use Lorisleiva\CronTranslator\CronTranslator;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Peon\Ui\ReadModel\JobStatus;

final class ReadTask
{
    private readonly string $lastJobStatus;

    public function __construct(
        public readonly string $taskId,
        public readonly string $name,
        public readonly string|null $schedule,
        public readonly string $commands,
        public readonly string|null $lastJobId,
        public readonly DateTimeImmutable|null $lastJobScheduledAt,
        public readonly DateTimeImmutable|null $lastJobStartedAt,
        public readonly DateTimeImmutable|null $lastJobSucceededAt,
        public readonly DateTimeImmutable|null $lastJobFailedAt,
        public readonly DateTimeImmutable|null $lastJobCanceledAt,
        public readonly string|null $lastJobMergeRequestUrl,
    ) {
        $this->lastJobStatus = $this->determineJobStatus($lastJobFailedAt, $lastJobCanceledAt, $lastJobSucceededAt, $lastJobStartedAt);
    }


    /**
     * @throws JsonException
     */
    public function getCommandsWithNewLines(): string
    {
        /** @var array<string> $commandsArray */
        $commandsArray = Json::decode($this->commands);

        return implode("\n", $commandsArray);
    }


    public function getLastJobActionTime(): \DateTimeImmutable|null
    {
        return $this->lastJobCanceledAt
            ?? $this->lastJobFailedAt
            ?? $this->lastJobSucceededAt
            ?? $this->lastJobStartedAt
            ?? $this->lastJobScheduledAt;
    }


    public function isJobPending(): bool
    {
        return $this->lastJobStatus === JobStatus::SCHEDULED;
    }


    public function isJobInProgress(): bool
    {
        return $this->lastJobStatus === JobStatus::IN_PROGRESS;
    }


    public function hasJobSucceeded(): bool
    {
        return $this->lastJobStatus === JobStatus::SUCCEEDED;
    }


    public function hasJobFailed(): bool
    {
        return $this->lastJobStatus === JobStatus::FAILED;
    }


    public function isJobCanceled(): bool
    {
        return $this->lastJobStatus === JobStatus::CANCELED;
    }


    public function getHumanReadableCron(): string
    {
        if ($this->schedule === null) {
            throw new \LogicException();
        }

        return CronTranslator::translate($this->schedule);
    }


    /**
     * @throws \Exception
     */
    public function getNextRunTime(): \DateTimeImmutable
    {
        if ($this->schedule === null) {
            throw new \LogicException();
        }

        $cronExpression = new CronExpression($this->schedule);
        $nextRun = $cronExpression->getNextRunDate();

        return \DateTimeImmutable::createFromMutable($nextRun);
    }


    private function determineJobStatus(
        null|DateTimeImmutable $failedAt,
        null|DateTimeImmutable $canceledAt,
        null|DateTimeImmutable $succeededAt,
        null|DateTimeImmutable $startedAt,
    ): string {
        if ($failedAt !== null) {
            return JobStatus::FAILED;
        }

        if ($canceledAt !== null) {
            return JobStatus::CANCELED;
        }

        if ($succeededAt !== null) {
            return JobStatus::SUCCEEDED;
        }

        if ($startedAt !== null) {
            return JobStatus::IN_PROGRESS;
        }

        return JobStatus::SCHEDULED;
    }
}
