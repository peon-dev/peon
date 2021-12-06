<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectSettingsController extends AbstractController
{
    public function __construct(
        private ProjectsCollection $projectsCollection
    ) {}


    #[Route(path: '/project/{projectId}/settings', name: 'project_settings')]
    public function __invoke(string $projectId): Response
    {
        try {
            $project = $this->projectsCollection->get(new ProjectId($projectId));

            return $this->render('project_settings.html.twig', [
                'activeProject' => $project,
            ]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
