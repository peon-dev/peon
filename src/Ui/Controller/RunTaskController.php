<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RunTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RunTaskController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly TasksCollection $tasksCollection,
        private readonly JobsCollection $jobsCollection,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    /**
     * @throws JobExecutionFailed
     * @throws JobHasFinishedAlready
     * @throws JobHasNotStartedYet
     * @throws JobHasStartedAlready
     * @throws JobNotFound
     */
    #[Route(path: '/task/run/{taskId}', name: 'run_task')]
    public function __invoke(string $taskId, UserId $userId): Response
    {
        try {
            $task = $this->tasksCollection->get(new TaskId($taskId));

            $this->checkUserAccess->toProject($userId, $task->projectId);

            $jobId = $this->jobsCollection->nextIdentity();

            $this->commandBus->dispatch(
                new RunTask(
                    new TaskId($taskId),
                    $jobId,
                )
            );

            return $this->redirectToRoute('project_overview', ['projectId' => $task->projectId]);
        } catch (TaskNotFound | ProjectNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }
    }
}
