<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;

final class DashboardControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $client->request('GET', '/');

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();

        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */

        // Projects
        self::assertCount(2, $crawler->filter('.dashboard-projects > div'));

        // Jobs
        self::assertCount(5, $crawler->filter('.jobs-list-table tbody tr'));
    }
}
