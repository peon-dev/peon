<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\TasksCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ListJobsController extends AbstractController
{
    public function __construct(
        private JobsCollection $jobsCollection,
        private ProjectsCollection $projectsCollection,
        private TasksCollection $tasksCollection,
    ) {}


    #[Route(path: '/', name: 'jobs_list', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('jobs_list.html.twig', [
            'jobs' => $this->jobsCollection->all(),
            'projects' => $this->projectsCollection->all(),
            'tasks' => $this->tasksCollection->all(),
        ]);
    }
}
