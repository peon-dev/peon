<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

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
         * @see \PHPMate\Tests\DataFixtures\DataFixtures
         */

        self::assertCount(1, $crawler->filter('.dashboard-projects > div'));
        self::assertCount(2, $crawler->filter('.jobs-list-table tbody tr'));
    }
}
