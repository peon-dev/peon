<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
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
        // Temporary solution, https://github.com/phpstan/phpstan-doctrine/issues/221
        /** @var array<Project> $rows */
        $rows = $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(Project::class, 'p')
            ->getQuery()
            ->getResult();

        return $rows;
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
