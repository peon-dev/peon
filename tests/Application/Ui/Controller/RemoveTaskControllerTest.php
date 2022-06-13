<?php

declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Task\TasksCollection;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RemoveTaskControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomTaskId = Uuid::uuid4()->toString();

        $client->request('GET', "/remove-task/$randomTaskId");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testNonExistingTaskWillShow404(): void
    {
        $client = self::createClient();
        $taskId = '00000000-0000-0000-0000-000000000000';

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/remove-task/$taskId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testPageCanBeRendered(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $tasksCountBeforeScenario = count($tasksCollection->all());
        $taskId = DataFixtures::USER_1_TASK_ID;
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/remove-task/$taskId");

        self::assertResponseRedirects("/projects/$projectId");

        self::assertCount(1 - $tasksCountBeforeScenario, $tasksCollection->all());
    }
}
