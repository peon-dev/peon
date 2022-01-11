<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Peon\Domain\Task\Task;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Ramsey\Uuid\Uuid;

final class DoctrineTasksCollection implements TasksCollection
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}
    
    
    public function nextIdentity(): TaskId
    {
        return new TaskId(Uuid::uuid4()->toString());
    }

    
    public function save(Task $task): void
    {
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    /**
     * @throws TaskNotFound
     */
    public function remove(TaskId $taskId): void
    {
        $task = $this->get($taskId);

        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }

    /**
     * @throws TaskNotFound
     */
    public function get(TaskId $taskId): Task
    {
        $task = $this->entityManager->find(Task::class, $taskId);

        return $task ?? throw new TaskNotFound();
    }

    /**
     * @return array<Task>
     */
    public function all(): array
    {
        // Temporary solution, https://github.com/phpstan/phpstan-doctrine/issues/221
        /** @var array<Task> $rows */
        $rows = $this->entityManager
            ->createQueryBuilder()
            ->select('t')
            ->from(Task::class, 't')
            ->getQuery()
            ->getResult();

        return $rows;
    }
}
