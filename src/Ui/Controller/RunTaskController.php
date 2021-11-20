<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Job\JobExecutionFailed;
use PHPMate\Domain\Job\JobHasFinishedAlready;
use PHPMate\Domain\Job\JobHasNoCommands;
use PHPMate\Domain\Job\JobHasNotStartedYet;
use PHPMate\Domain\Job\JobHasStartedAlready;
use PHPMate\Domain\Job\JobNotFound;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\Ui\FlashType;
use PHPMate\UseCase\RunTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RunTaskController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
        private TasksCollection $tasksCollection
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

            return $this->redirectToRoute('project_detail', ['projectId' => $task->projectId]);
        } catch (TaskNotFound | ProjectNotFound) {
            throw $this->createNotFoundException();
        } catch (JobHasNoCommands) {
            $this->addFlash(FlashType::WARNING, 'Task must have some commands to run! Please add some first.');
        }

        return $this->redirectToRoute('dashboard');
    }
}
