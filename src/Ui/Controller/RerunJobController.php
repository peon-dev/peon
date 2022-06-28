<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RerunJob;
use Peon\UseCase\RunTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RerunJobController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
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
    #[Route(path: '/rerun-job/{jobId}', name: 'rerun_job')]
    #[Route(path: '/projects/rerun-job/{jobId}', name: 'project_rerun_job')]
    public function __invoke(
        JobId $jobId,
        UserId $userId,
        Request $request,
    ): Response
    {
        try {
            $job = $this->jobsCollection->get($jobId);

            $this->checkUserAccess->toProject($userId, $job->projectId);

            $newJobId = $this->jobsCollection->nextIdentity();

            $this->commandBus->dispatch(
                new RerunJob($jobId, $newJobId),
            );

            $route = $request->attributes->get('_route');
            if ($route === 'project_rerun_job') {
                return $this->redirectToRoute('project_overview', ['projectId' => $job->projectId]);
            }
            return $this->redirectToRoute('job_detail', ['jobId' => $newJobId]);
        } catch (TaskNotFound | ProjectNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }
    }
}
