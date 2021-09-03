<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Task\TaskId;
use PHPMate\UseCase\DeleteProjectCommand;
use PHPMate\UseCase\DeleteProject;
use PHPMate\UseCase\RemoveTaskCommand;
use PHPMate\UseCase\RemoveTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DeleteProjectController extends AbstractController
{
    public function __construct(
        private DeleteProject $deleteProjectUseCase
    ) {}


    #[Route(path: '/delete-project/{projectId}', name: 'delete_project')]
    public function __invoke(string $projectId): Response
    {
        try {
            $this->deleteProjectUseCase->handle(
                new DeleteProjectCommand(
                    new ProjectId($projectId)
                )
            );
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('dashboard');
    }
}
