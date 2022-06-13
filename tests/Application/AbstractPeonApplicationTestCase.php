<?php

declare(strict_types=1);

namespace Peon\Tests\Application;

use Peon\Domain\User\UsersCollection;
use Peon\Domain\User\Value\UserId;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractPeonApplicationTestCase extends WebTestCase
{
    public function loginUserWithId(KernelBrowser $browser, string $userId): void
    {
        $container = self::getContainer();

        $usersCollection = $container->get(UsersCollection::class);
        $user = $usersCollection->get(new UserId($userId));

        $browser->loginUser($user);
    }
}
