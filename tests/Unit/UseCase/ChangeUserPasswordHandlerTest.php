<?php
declare(strict_types=1);

namespace Peon\Tests\Unit\UseCase;

use Peon\Domain\User\HashPlainTextPassword;
use Peon\Domain\User\User;
use Peon\Domain\User\UsersCollection;
use Peon\Domain\User\Value\UserId;
use Peon\UseCase\ChangeUserPassword;
use Peon\UseCase\ChangeUserPasswordHandler;
use PHPUnit\Framework\TestCase;

final class ChangeUserPasswordHandlerTest extends TestCase
{
    public function test(): void
    {
        $hashPlainTextPassword = new class implements HashPlainTextPassword {
            public function hash(string $plainTextPassword): string
            {
                return 'hashed_' . $plainTextPassword;
            }
        };

        $userId = new UserId('');

        $userMock = $this->createTestProxy(User::class, [
            $userId,
            'username',
            'password',
        ]);
        $userMock->expects(self::once())
            ->method('changePassword')
            ->with('hashed_new');

        $usersCollectionMock = $this->createMock(UsersCollection::class);
        $usersCollectionMock->expects(self::once())
            ->method('get')
            ->with($userId)
            ->willReturn($userMock);

        $handler = new ChangeUserPasswordHandler(
            $usersCollectionMock,
            $hashPlainTextPassword,
        );

        $handler->__invoke(
            new ChangeUserPassword($userId, 'new')
        );
    }
}
