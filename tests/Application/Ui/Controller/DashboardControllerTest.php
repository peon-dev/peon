<?php
declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DashboardControllerTest extends WebTestCase
{
    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();

        /**
         * Data are populated from data fixtures
         * @see \Peon\Tests\DataFixtures\DataFixtures
         */

        self::assertCount(2, $crawler->filter('.dashboard-projects > div'));
        self::assertCount(4, $crawler->filter('.jobs-list-table tbody tr'));
    }
}
