<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Job\JobId;
use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\UseCase\DeleteProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class JobDetailController extends AbstractController
{
    public function __construct(
        private JobsCollection $jobsCollection
    ) {}


    #[Route(path: '/job/{jobId}', name: 'job_detail')]
    public function __invoke(string $jobId): Response
    {
        try {
            $job = $this->jobsCollection->get(new JobId($jobId));

        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->render('job_detail.html.twig', [
            'activeJob' => $job,
        ]);
    }
}
