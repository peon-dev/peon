<?php

declare(strict_types=1);

namespace Peon\Domain\Worker;

use JetBrains\PhpStorm\Immutable;
use Lcobucci\Clock\Clock;
use Peon\Domain\Worker\Value\WorkerId;

class WorkerStatus
{
    #[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
    public \DateTimeImmutable $lastSeenAt;


    public function __construct(
        public readonly WorkerId $workerId,
        Clock $clock
    ) {
        $this->lastSeenAt = $clock->now();
    }


    public function updateLiveness(Clock $clock): void
    {
        $this->lastSeenAt = $clock->now();
    }
}
