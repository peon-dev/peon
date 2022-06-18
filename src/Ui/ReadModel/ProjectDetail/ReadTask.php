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
        if ($lastJobFailedAt !== null) {
            $this->lastJobStatus = JobStatus::FAILED;
        } elseif ($this->lastJobCanceledAt !== null) {
            $this->lastJobStatus = JobStatus::CANCELED;
        } elseif ($lastJobSucceededAt !== null) {
            $this->lastJobStatus = JobStatus::SUCCEEDED;
        } elseif ($lastJobStartedAt !== null) {
            $this->lastJobStatus = JobStatus::IN_PROGRESS;
        } else {
            $this->lastJobStatus = JobStatus::SCHEDULED;
        }
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
}
