<?php

declare(strict_types=1);

namespace Peon\Domain\Worker;

use Peon\Domain\Worker\Exception\WorkerNotReportedAnythingYet;
use Peon\Domain\Worker\Value\WorkerId;

interface WorkerStatusesCollection
{
    /**
     * @throws WorkerNotReportedAnythingYet
     */
    public function get(WorkerId $workerId): WorkerStatus;

    public function save(WorkerStatus $workerStatus): void;
}
