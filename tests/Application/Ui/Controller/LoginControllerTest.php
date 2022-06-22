<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;

class LoginControllerTest extends AbstractPeonApplicationTestCase
{
    public function testInvalidCredentials(): void
    {
        $this->markTestIncomplete('todo');
    }


    public function testUserCanLogIn(): void
    {
        $this->markTestIncomplete('todo');
    }


    public function testAlreadyLoggedInUserWillBeRedirectedToDashboard(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', '/login');

        self::assertResponseRedirects('/');
    }
}
