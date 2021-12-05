<?php

declare(strict_types=1);

namespace PHPMate\Ui\ReadModel\ProjectDetail;

use PHPMate\Domain\Cookbook\Value\RecipeName;

final class ReadRecipe
{
    public function __construct(
        public string $title,
        public string $humanReadableCron,
        public string $nextRunTime,
        public RecipeName $recipeName,
        public ?string $lastJobId,
        public \DateTimeImmutable $lastJobScheduledAt,
        public ?\DateTimeImmutable $lastJobStartedAt,
        public ?\DateTimeImmutable $lastJobSucceededAt,
        public ?\DateTimeImmutable $lastJobFailedAt,
    )
    {
    }
}
