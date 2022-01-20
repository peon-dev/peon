<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\PhpApplication\Value\BuildConfiguration;
use Peon\Domain\Project\Value\ProjectId;

final class ConfigureProject
{
    public function __construct(
        public readonly ProjectId $projectId,
        public readonly BuildConfiguration $buildConfiguration,
    ) {}
}
