<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\UseCase\RemoveTask;
use PHPMate\UseCase\RemoveTaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RemoveTaskController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
        private TasksCollection $tasksCollection
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
