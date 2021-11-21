<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DefineTaskControllerTest extends WebTestCase
{
    // TODO: test 404

    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $projectId = DataFixtures::PROJECT_ID;

        $client->request('GET', "/define-task/$projectId");

        self::assertResponseIsSuccessful();

        // TODO: maybe add more assertions later
    }

    // TODO: test form submission
}
