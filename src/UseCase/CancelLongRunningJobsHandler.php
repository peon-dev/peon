<?php

declare(strict_types=1);

namespace Peon\UseCase;

final class CancelLongRunningJobsHandler
{
    public function __invoke(CancelLongRunningJobs $command): void
    {
        // get list of long running jobs

        // cancel :-)
    }
}
