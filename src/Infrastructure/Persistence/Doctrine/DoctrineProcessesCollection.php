<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Peon\Domain\Process\Process;
use Peon\Domain\Process\ProcessesCollection;
use Peon\Domain\Process\Value\ProcessId;
use Ramsey\Uuid\Uuid;

final class DoctrineProcessesCollection implements ProcessesCollection
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}


    public function nextIdentity(): ProcessId
    {
        return new ProcessId(Uuid::uuid4()->toString());
    }


    public function save(Process $process): void
    {
        $this->entityManager->persist($process);
        $this->entityManager->flush();
    }
}
