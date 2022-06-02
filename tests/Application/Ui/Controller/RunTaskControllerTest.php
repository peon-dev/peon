<?php

declare(strict_types=1);

namespace Peon\Tests\Application\Ui\Controller;

use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\JobsCollection;
use Peon\Tests\DataFixtures\DataFixtures;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RunTaskControllerTest extends WebTestCase
{
    public function testPageIsProtectedWithLogin(): void
    {
        $client = self::createClient();

        $randomTaskId = Uuid::uuid4()->toString();

        $client->request('GET', "/task/run/$randomTaskId");

        self::assertResponseRedirects('http://localhost/login');
    }


    public function testNonExistingTaskWillShow404(): void
    {
        $client = self::createClient();
        $taskId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/task/run/$taskId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testTaskCanBeRunAndJobWillBeCreated(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $jobsCollection = $container->get(JobsCollection::class);
        $jobsCountBeforeScenario = count($jobsCollection->all());
        $taskId = DataFixtures::TASK_ID;

        $client->request('GET', "/task/run/$taskId");

        $projectId = DataFixtures::PROJECT_1_ID;
        self::assertResponseRedirects("/projects/$projectId");

        self::assertCount(1 + $jobsCountBeforeScenario, $jobsCollection->all());
    }
}
