<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CookbookControllerTest extends WebTestCase
{
    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();

        $client->request('GET', '/cookbook');

        self::assertResponseIsSuccessful();

        // TODO: maybe add more assertions later
    }
}
