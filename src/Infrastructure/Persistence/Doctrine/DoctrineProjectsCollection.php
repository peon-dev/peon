<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use Ramsey\Uuid\Uuid;

final class DoctrineProjectsCollection implements ProjectsCollection
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function get(ProjectId $projectId): Project
    {
        $project = $this->entityManager->find(Project::class, $projectId);

        return $project ?? throw new ProjectNotFound();
    }


    public function save(Project $project): void
    {
        $this->entityManager->persist($project);
        $this->entityManager->flush();
    }


    /**
     * @return array<Project>
     */
    public function all(): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(Project::class, 'p')
            ->getQuery()
            ->getResult();
    }


    public function nextIdentity(): ProjectId
    {
        return new ProjectId(Uuid::uuid4()->toString());
    }


    /**
     * @throws ProjectNotFound
     */
    public function remove(ProjectId $projectId): void
    {
        $project = $this->get($projectId);

        $this->entityManager->remove($project);
        $this->entityManager->flush();
    }
}
