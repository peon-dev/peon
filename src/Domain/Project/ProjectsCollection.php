<?php

declare(strict_types=1);

namespace PHPMate\Domain\Project;

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
