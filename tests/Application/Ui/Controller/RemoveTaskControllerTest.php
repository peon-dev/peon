<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RemoveTaskControllerTest extends WebTestCase
{
    public function testNonExistingTaskWillShow404(): void
    {
        $client = self::createClient();
        $taskId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/remove-task/$taskId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $tasksCountBeforeScenario = count($tasksCollection->all());
        $taskId = DataFixtures::TASK_ID;
        $projectId = DataFixtures::PROJECT_ID;

        $client->request('GET', "/remove-task/$taskId");

        self::assertResponseRedirects("/projects/$projectId");

        self::assertCount(1 - $tasksCountBeforeScenario, $tasksCollection->all());
    }
}
