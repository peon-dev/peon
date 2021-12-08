<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Tests\DataFixtures\DataFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DefineTaskControllerTest extends WebTestCase
{
    public function testNonExistingProjectWillShow404(): void
    {
        $client = self::createClient();
        $projectId = '00000000-0000-0000-0000-000000000000';

        $client->request('GET', "/define-task/$projectId");

        self::assertResponseStatusCodeSame(404);
    }


    public function testTaskWillBeAddedUsingForm(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $tasksCountBeforeScenario = count($tasksCollection->all());

        $projectId = DataFixtures::PROJECT_1_ID;
        $crawler = $client->request('GET', "/define-task/$projectId");

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('submit')->form();

        $client->submit($form, [
            $form->getName() . '[name]' => 'Test',
            $form->getName() . '[schedule]' => '* * * * *',
            $form->getName() . '[commands]' => 'command',
        ]);

        self::assertResponseRedirects("/projects/$projectId");

        self::assertCount(1 + $tasksCountBeforeScenario, $tasksCollection->all());
    }
}
