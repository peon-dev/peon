<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadTasks;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectDetailController extends AbstractController
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private ProvideReadTasks $provideReadTasks
    ) {}


    #[Route(path: '/project/{projectId}', name: 'project_detail')]
    public function __invoke(string $projectId): Response
    {
        try {
            return $this->render('project_detail.html.twig', [
                'activeProject' => $this->projectsCollection->get(new ProjectId($projectId)),
                'tasks' => $this->provideReadTasks->provide($projectId),
            ]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
