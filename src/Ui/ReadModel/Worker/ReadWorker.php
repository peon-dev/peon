<?php

declare(strict_types=1);

namespace Peon\Ui\ReadModel\Worker;

final class ReadWorker
{
    public function __construct(
        public readonly string $workerId,
        public readonly \DateTimeImmutable $lastSeenAt,
    ) {
    }
}
