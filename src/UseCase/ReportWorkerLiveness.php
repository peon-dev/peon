<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Worker\Value\WorkerId;

final class ReportWorkerLiveness
{
    public function __construct(
        public readonly WorkerId $workerId,
    ) {
    }
}
