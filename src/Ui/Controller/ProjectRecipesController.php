<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectRecipesController extends AbstractController
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
    ) {}


    #[Route(path: '/project/{projectId}/recipes', name: 'project_recipes')]
    public function __invoke(string $projectId): Response
    {
        try {
            return $this->render('project_recipes.html.twig', [
                'activeProject' => $this->projectsCollection->get(new ProjectId($projectId)),
            ]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
