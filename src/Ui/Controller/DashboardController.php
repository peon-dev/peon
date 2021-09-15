<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Job\JobsCollection;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Ui\ReadModel\Dashboard\ProvideReadJobs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    public function __construct(
        private JobsCollection $jobsCollection,
        private ProjectsCollection $projectsCollection,
        private TasksCollection $tasksCollection,
        private ProvideReadJobs $provideReadJobs,
    ) {}


    #[Route(path: '/', name: 'dashboard', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('dashboard.html.twig', [
            'jobs' => $this->provideReadJobs->provide(10),
            'projects' => $this->projectsCollection->all(),
            'tasks' => $this->tasksCollection->all(),
        ]);
    }
}
