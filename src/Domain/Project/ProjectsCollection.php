<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

use PHPMate\Domain\Project\Exceptions\ProjectNotFound;

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
