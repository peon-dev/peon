<?php

declare(strict_types=1);

namespace Peon\Domain\Project;

use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;

interface ProjectsCollection
{
    /**
     * @throws ProjectNotFound
     */
    public function get(ProjectId $projectId): Project;

    public function save(Project $project): void;

    /**
     * @return array<Project>
     */
    public function all(): array;

    public function nextIdentity(): ProjectId;

    /**
     * @throws ProjectNotFound
     */
    public function remove(ProjectId $projectId): void;
}
