<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadTasks;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectDetailController extends AbstractController
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private ProvideReadTasks $provideReadTasks,
        private RecipesCollection $recipesCollection,
    ) {}


    #[Route(path: '/project/{projectId}', name: 'project_detail')]
    public function __invoke(string $projectId): Response
    {
        try {
            return $this->render('project_detail.html.twig', [
                'activeProject' => $this->projectsCollection->get(new ProjectId($projectId)),
                'tasks' => $this->provideReadTasks->provide($projectId),
                'recipesCollection' => $this->recipesCollection, // TODO: this should not be in template, remove later, ugly pattern
            ]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
