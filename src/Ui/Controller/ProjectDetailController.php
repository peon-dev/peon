<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Ui\ReadModel\Dashboard\ProvideProjectReadJobs;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideProjectReadRecipes;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadTasks;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectDetailController extends AbstractController
{
    public function __construct(
        private ProjectsCollection        $projectsCollection,
        private ProvideReadTasks          $provideReadTasks,
        private ProvideProjectReadJobs    $provideProjectReadJobs,
        private ProvideProjectReadRecipes $provideProjectReadRecipes,
    ) {}


    #[Route(path: '/project/{projectId}', name: 'project_detail')]
    public function __invoke(string $projectId): Response
    {
        try {
            $project = $this->projectsCollection->get(new ProjectId($projectId));

            return $this->render('project_detail.html.twig', [
                'activeProject' => $project,
                'tasks' => $this->provideReadTasks->provide($project->projectId),
                'jobs' => $this->provideProjectReadJobs->provide($project->projectId, 20),
                'recipes' => $this->provideProjectReadRecipes->provide($project->projectId),
            ]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
