<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RemoveTaskControllerTest extends WebTestCase
{
    // TODO: test 404

    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $taskId = DataFixtures::TASK_ID;

        $client->request('GET', "/remove-task/$taskId");

        $projectId = DataFixtures::PROJECT_ID;
        self::assertResponseRedirects("/project/$projectId");
    }
}
