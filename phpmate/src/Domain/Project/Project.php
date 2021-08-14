<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use PHPMate\Domain\Tools\Git\RemoteGitRepository;

interface Project
{
    public function getGitRepository(): RemoteGitRepository;
}
