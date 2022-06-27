<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;

final class WorkersControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $client->request('GET', '/workers');

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', '/workers');

        self::assertResponseIsSuccessful();
        self::assertSame('2', $crawler->filter('#queued-jobs-count')->innerText());
    }
}
