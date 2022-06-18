<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\User\HashPlainTextPassword;
use Peon\Domain\User\User;
use Peon\Domain\User\UsersCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class RegisterUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UsersCollection $usersCollection,
        private readonly HashPlainTextPassword $hashPlainTextPassword,
    ) {}


    public function __invoke(RegisterUser $command): void
    {
        $userId = $this->usersCollection->nextIdentity();
        $hashedPassword = $this->hashPlainTextPassword->hash($command->plainTextPassword);

        $user = new User(
            $userId,
            $command->username,
            $hashedPassword,
        );

        $this->usersCollection->save($user);
    }
}
