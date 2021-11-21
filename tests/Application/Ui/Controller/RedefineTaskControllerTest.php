<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RedefineTaskControllerTest extends WebTestCase
{
    public function testNonExistingTaskWillShow404(): void
    {
        $client = self::createClient();
        $taskId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/redefine-task/$taskId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $taskId = DataFixtures::TASK_ID;

        $client->request('GET', "/redefine-task/$taskId");

        self::assertResponseIsSuccessful();

        // TODO: maybe add more assertions later
    }

    // TODO: test form submission
}
