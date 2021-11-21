<?php
declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateProjectControllerTest extends WebTestCase
{
    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();

        $client->request('GET', '/create-project');

        self::assertResponseIsSuccessful();

        // TODO: maybe add more assertions later
    }
}
