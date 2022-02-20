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

#[Immutable]
final class ReadTask
{
    private string $lastJobStatus = JobStatus::SCHEDULED;


    public function __construct(
        public string $taskId,
        public string $name,
        public string|null $schedule,
        public string $commands,
        public string|null $lastJobId,
        public DateTimeImmutable|null $lastJobScheduledAt,
        public DateTimeImmutable|null $lastJobStartedAt,
        public DateTimeImmutable|null $lastJobSucceededAt,
        public DateTimeImmutable|null $lastJobFailedAt,
        public DateTimeImmutable|null $lastJobCanceledAt,
        public string|null $lastJobMergeRequestUrl,
    ) {
        if ($lastJobFailedAt !== null) {
            $this->lastJobStatus = JobStatus::FAILED;
        } elseif ($this->lastJobCanceledAt !== null) {
            $this->lastJobStatus = JobStatus::CANCELED;
        } elseif ($lastJobSucceededAt !== null) {
            $this->lastJobStatus = JobStatus::SUCCEEDED;
        } elseif ($lastJobStartedAt !== null) {
            $this->lastJobStatus = JobStatus::IN_PROGRESS;
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
