<?php

declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\Task\Value\TaskId;
use Peon\Tests\DataFixtures\DataFixtures;
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


    public function testTaskCanBeChangedUsingForm(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $taskId = DataFixtures::TASK_ID;
        $projectId = DataFixtures::PROJECT_1_ID;

        $crawler = $client->request('GET', "/redefine-task/$taskId");

        $form = $crawler->selectButton('submit')->form();

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
        $taskId = DataFixtures::TASK_ID;
        $jobsCollection = $container->get(JobsCollection::class);
        $jobsCountBeforeScenario = count($jobsCollection->all());

        $crawler = $client->request('GET', "/redefine-task/$taskId");

        $form = $crawler->selectButton('save-and-run')->form();

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
