<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Peon\Domain\User\Exception\UserNotFound;
use Peon\Domain\User\User;
use Peon\Domain\User\UsersCollection;
use Peon\Domain\User\Value\UserId;
use Ramsey\Uuid\Uuid;

final class DoctrineUsersCollection implements UsersCollection
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}


    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }


    public function nextIdentity(): UserId
    {
        return new UserId(Uuid::uuid4()->toString());
    }


    public function get(UserId $userId): User
    {
        $project = $this->entityManager->find(User::class, $userId);

        return $project ?? throw new UserNotFound();
    }
}
