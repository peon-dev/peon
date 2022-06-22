<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\User\Value\UserId;
use Peon\Ui\ReadModel\Job\ProvideReadJobById;
use Peon\Ui\ReadModel\Process\ProvideReadProcessesByJobId;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class JobDetailController extends AbstractController
{
    public function __construct(
        private readonly ProvideReadProjectDetail $provideReadProjectDetail,
        private readonly ProvideReadJobById $provideReadJobById,
        private readonly ProvideReadProcessesByJobId $provideReadProcessesByJobId,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    #[Route(path: '/job/{jobId}', name: 'job_detail')]
    public function __invoke(string $jobId, UserInterface $user): Response
    {
        $userId = new UserId($user->getUserIdentifier());

        try {
            $job = $this->provideReadJobById->provide(new JobId($jobId));

            $this->checkUserAccess->toProject($userId, new ProjectId($job->projectId));

            $processes = $this->provideReadProcessesByJobId->provide(new JobId($jobId));
            $project = $this->provideReadProjectDetail->provide(new ProjectId($job->projectId));

        } catch (JobNotFound | ProjectNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }

        return $this->render('job_detail.html.twig', [
            'activeJob' => $job,
            'processes' => $processes,
            'activeProject' => $project,
        ]);
    }
}
