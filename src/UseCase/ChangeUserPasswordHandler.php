<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\User\Exception\UserNotFound;
use Peon\Domain\User\HashPlainTextPassword;
use Peon\Domain\User\UsersCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class ChangeUserPasswordHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UsersCollection $usersCollection,
        private readonly HashPlainTextPassword $hashPlainTextPassword,
    ) {}


    /**
     * @throws UserNotFound
     */
    public function __invoke(ChangeUserPassword $command): void
    {
        $user = $this->usersCollection->get($command->userId);
        $hashedNewPassword = $this->hashPlainTextPassword->hash($command->plainTextNewPassword);

        $user->changePassword($hashedNewPassword);

        $this->usersCollection->save($user);
    }
}
