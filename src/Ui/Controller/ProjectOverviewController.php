<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Ui\ReadModel\Dashboard\ProvideProjectReadJobs;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideProjectReadRecipes;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadTasks;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectOverviewController extends AbstractController
{
    public function __construct(
        private ProvideReadProjectDetail $provideReadProjectDetail,
        private ProvideReadTasks          $provideReadTasks,
        private ProvideProjectReadJobs    $provideProjectReadJobs,
        private ProvideProjectReadRecipes $provideProjectReadRecipes,
    ) {}


    #[Route(path: '/projects/{projectId}', name: 'project_overview')]
    public function __invoke(string $projectId): Response
    {
        try {
            $id = new ProjectId($projectId);
            $project = $this->provideReadProjectDetail->provide($id);

            return $this->render('project_overview.html.twig', [
                'activeProject' => $project,
                'tasks' => $this->provideReadTasks->provide($id),
                'jobs' => $this->provideProjectReadJobs->provide($id, 20),
                'recipes' => $this->provideProjectReadRecipes->provide($id),
            ]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
