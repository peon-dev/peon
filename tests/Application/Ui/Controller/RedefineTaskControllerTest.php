<?php

declare(strict_types=1);

namespace PHPMate\Tests\Application\Ui\Controller;

use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Domain\Task\Value\TaskId;
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


    public function testTaskCanBeChangedUsingForm(): void
    {
        $client = self::createClient();
        $container = self::getContainer();
        $tasksCollection = $container->get(TasksCollection::class);
        $taskId = DataFixtures::TASK_ID;
        $projectId = DataFixtures::PROJECT_ID;

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

        self::assertResponseRedirects("/project/$projectId");

        $task = $tasksCollection->get(new TaskId($taskId));
        self::assertSame('New name', $task->name);
        self::assertSame('* * * * *', $task->schedule?->getExpression());
        self::assertSame(['New command 1', 'New command 2'], $task->commands);
    }
}
