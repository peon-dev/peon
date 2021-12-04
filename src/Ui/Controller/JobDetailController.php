<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Job\Value\JobId;
use PHPMate\Domain\Job\Exception\JobNotFound;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class JobDetailController extends AbstractController
{
    public function __construct(
        private JobsCollection $jobsCollection,
        private ProjectsCollection $projectsCollection,
    ) {}


    #[Route(path: '/job/{jobId}', name: 'job_detail')]
    public function __invoke(string $jobId): Response
    {
        try {
            $job = $this->jobsCollection->get(new JobId($jobId));
            $project = $this->projectsCollection->get($job->projectId);

        } catch (JobNotFound | ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->render('job_detail.html.twig', [
            'activeJob' => $job,
            'activeProject' => $project,
        ]);
    }
}
