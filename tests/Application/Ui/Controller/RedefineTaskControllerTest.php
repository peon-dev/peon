<?php

declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\Task\Value\TaskId;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RedefineTaskControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomTaskId = Uuid::uuid4()->toString();

        $client->request('GET', "/redefine-task/$randomTaskId");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testCanNotRedefineTaskForForeignProject(): void
    {
        $client = self::createClient();
        $anotherUserTaskId = DataFixtures::USER_2_TASK_ID;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        foreach (['GET', 'POST'] as $method) {
            $client->request($method, "/redefine-task/$anotherUserTaskId");

            // Intentionally 404, and not 401/403
            self::assertResponseStatusCodeSame(404);
        }
    }


    public function testNonExistingTaskWillShow404(): void
    {
        $client = self::createClient();
        $taskId = '00000000-0000-0000-0000-000000000000';

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/redefine-task/$taskId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testTaskCanBeChangedUsingForm(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $taskId = DataFixtures::USER_1_TASK_ID;
        $projectId = DataFixtures::USER_1_PROJECT_1_ID;

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', "/redefine-task/$taskId");

        $form = $crawler->selectButton('Save')->form();

        $commands = <<<STRING
New command 1
New command 2
STRING;

        $client->submit($form, [
            $form->getName() . '[name]' => 'New name',
            $form->getName() . '[schedule]' => '* * * * *',
            $form->getName() . '[commands]' => $commands,
        ]);

        self::assertResponseRedirects("/projects/$projectId");

        $task = $tasksCollection->get(new TaskId($taskId));
        self::assertSame('New name', $task->name);
        self::assertSame('* * * * *', $task->schedule?->getExpression());
        self::assertSame(['New command 1', 'New command 2'], $task->commands);
    }



    public function testJobWillBeScheduledUsingSaveAndRunButton(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $taskId = DataFixtures::USER_1_TASK_ID;
        $jobsCollection = $container->get(JobsCollection::class);
        $jobsCountBeforeScenario = count($jobsCollection->all());

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $crawler = $client->request('GET', "/redefine-task/$taskId");

        $form = $crawler->selectButton('Save & Run')->form();

        $commands = <<<STRING
New command 1
New command 2
STRING;

        $client->submit($form, [
            $form->getName() . '[name]' => 'New name',
            $form->getName() . '[schedule]' => '* * * * *',
            $form->getName() . '[commands]' => $commands,
        ]);

        $jobs = $jobsCollection->all();
        $job = $jobs[array_key_last($jobs)];

        self::assertResponseRedirects("/job/" . $job->jobId->id);

        $task = $tasksCollection->get(new TaskId($taskId));
        self::assertSame('New name', $task->name);
        self::assertSame('* * * * *', $task->schedule?->getExpression());
        self::assertSame(['New command 1', 'New command 2'], $task->commands);
        self::assertCount(1 + $jobsCountBeforeScenario, $jobsCollection->all());
    }
}
