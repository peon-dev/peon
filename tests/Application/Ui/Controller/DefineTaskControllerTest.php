<?php

declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Task\TasksCollection;
use Peon\Tests\Application\AbstractPeonApplicationTestCase;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DefineTaskControllerTest extends AbstractPeonApplicationTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomProjectId = Uuid::uuid4()->toString();

        $client->request('GET', "/define-task/$randomProjectId");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $client->request('GET', "/define-task/$projectId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testTaskWillBeAddedUsingForm(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $tasksCountBeforeScenario = count($tasksCollection->all());

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $projectId = DataFixtures::PROJECT_1_ID;
        $crawler = $client->request('GET', "/define-task/$projectId");

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form();

        $client->submit($form, [
            $form->getName() . '[name]' => 'Test',
            $form->getName() . '[schedule]' => '* * * * *',
            $form->getName() . '[commands]' => 'command',
        ]);

        self::assertResponseRedirects("/projects/$projectId");

        self::assertCount(1 + $tasksCountBeforeScenario, $tasksCollection->all());
    }


    public function testJobWillBeScheduledUsingSaveAndRunButton(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $tasksCountBeforeScenario = count($tasksCollection->all());
        $jobsCollection = $container->get(JobsCollection::class);
        $jobsCountBeforeScenario = count($jobsCollection->all());

        $this->loginUserWithId($client, DataFixtures::USER_1_ID);

        $projectId = DataFixtures::PROJECT_1_ID;
        $crawler = $client->request('GET', "/define-task/$projectId");

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save & Run')->form();

        $client->submit($form, [
            $form->getName() . '[name]' => 'Test',
            $form->getName() . '[schedule]' => '* * * * *',
            $form->getName() . '[commands]' => 'command',
        ]);

        $jobs = $jobsCollection->all();
        $job = $jobs[array_key_last($jobs)];

        self::assertResponseRedirects("/job/" . $job->jobId->id);

        self::assertCount(1 + $tasksCountBeforeScenario, $tasksCollection->all());
        self::assertCount(1 + $jobsCountBeforeScenario, $jobsCollection->all());
    }
}
