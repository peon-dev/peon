<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Job\Exception\JobExecutionFailed;
use PHPMate\Domain\Job\Exception\JobHasFinishedAlready;
use PHPMate\Domain\Job\Exception\JobHasNotStartedYet;
use PHPMate\Domain\Job\Exception\JobHasStartedAlready;
use PHPMate\Domain\Job\Exception\JobNotFound;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\UseCase\RunTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RunTaskController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
        private TasksCollection $tasksCollection,
    ) {}


    /**
     * @throws JobExecutionFailed
     * @throws JobHasFinishedAlready
     * @throws JobHasNotStartedYet
     * @throws JobHasStartedAlready
     * @throws JobNotFound
     */
    #[Route(path: '/task/run/{taskId}', name: 'run_task')]
    public function __invoke(string $taskId): Response
    {
        try {
            $task = $this->tasksCollection->get(new TaskId($taskId));

            $this->commandBus->dispatch(
                new RunTask(
                    new TaskId($taskId)
                )
            );

            return $this->redirectToRoute('project_overview', ['projectId' => $task->projectId]);
        } catch (TaskNotFound | ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
