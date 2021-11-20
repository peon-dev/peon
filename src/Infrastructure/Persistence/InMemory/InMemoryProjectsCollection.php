<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\InMemory;

use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;

final class InMemoryProjectsCollection implements ProjectsCollection
{
    /**
     * @var array<string, Project>
     */
    private array $projects = [];


    public function nextIdentity(): ProjectId
    {
        return new ProjectId((string) count($this->projects));
    }


    public function save(Project $project): void
    {
        $this->projects[$project->projectId->id] = $project;
    }


    /**
     * @throws \PHPMate\Domain\Project\Exception\ProjectNotFound
     */
    public function remove(ProjectId $projectId): void
    {
        if (isset($this->projects[$projectId->id]) === false) {
            throw new ProjectNotFound();
        }

        unset($this->projects[$projectId->id]);
    }


    /**
     * @throws \PHPMate\Domain\Project\Exception\ProjectNotFound
     */
    public function get(ProjectId $projectId): Project
    {
        return $this->projects[$projectId->id] ?? throw new ProjectNotFound();
    }


    /**
     * @return array<string, Project>
     */
    public function all(): array
    {
        return $this->projects;
    }
}
