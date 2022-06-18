<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RemoveTask;
use Peon\UseCase\RemoveTaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RemoveTaskController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly TasksCollection $tasksCollection,
    ) {}


    #[Route(path: '/remove-task/{taskId}', name: 'remove_task')]
    public function __invoke(string $taskId): Response
    {
        try {
            $task = $this->tasksCollection->get(new TaskId($taskId));

            $this->commandBus->dispatch(
                new RemoveTask(
                    new TaskId($taskId)
                )
            );

            return $this->redirectToRoute('project_overview', ['projectId' => $task->projectId]);
        } catch (TaskNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
