<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;

class LoginControllerTest extends AbstractPeonApplicationTestCase
{
    public function testInvalidCredentials(): void
    {
        $client = self::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Log in', [
            '_username' => 'totally-invalid',
            '_password' => 'totally-invalid',
        ]);

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testUserCanLogIn(): void
    {
        $client = self::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Log in', [
            '_username' => DataFixtures::USER_1_USERNAME,
            '_password' => DataFixtures::USER_PASSWORD,
        ]);

        self::assertResponseRedirects('http://localhost/');
    }


    public function testAlreadyLoggedInUserWillBeRedirectedToDashboard(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', '/login');

        self::assertResponseRedirects('/');
    }
}
