<?php

declare(strict_types=1);

namespace Peon\Domain\Application\Value;

use Peon\Domain\Job\Value\JobId;

final class TemporaryApplication
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly ApplicationLanguage $language,
        public readonly ApplicationGitRepositoryClone $gitRepository,
    ) {}
}
