<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RunTaskControllerTest extends WebTestCase
{
    // TODO: test 404

    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $taskId = DataFixtures::TASK_ID;

        $client->request('GET', "/task/run/$taskId");

        self::assertResponseIsSuccessful();

        // TODO test redirect
    }
}
