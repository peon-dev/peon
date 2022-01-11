<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\InMemory;

use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;

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
     * @throws ProjectNotFound
     */
    public function remove(ProjectId $projectId): void
    {
        if (isset($this->projects[$projectId->id]) === false) {
            throw new ProjectNotFound();
        }

        unset($this->projects[$projectId->id]);
    }


    /**
     * @throws ProjectNotFound
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
