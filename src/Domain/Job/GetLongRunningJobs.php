<?php

declare(strict_types=1);

namespace Peon\Domain\Job;

interface GetLongRunningJobs
{
    /**
     * @return array<Job>
     */
    public function olderThanHours(int $hours): array;
}
