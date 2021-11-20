<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\Exceptions\ProjectNotFound;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\UseCase\DeleteProject;
use PHPMate\UseCase\DeleteProjectHandler;
use PHPMate\UseCase\RemoveTask;
use PHPMate\UseCase\RemoveTaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DeleteProjectController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus
    ) {}


    #[Route(path: '/delete-project/{projectId}', name: 'delete_project')]
    public function __invoke(string $projectId): Response
    {
        try {
            $this->commandBus->dispatch(
                new DeleteProject(
                    new ProjectId($projectId)
                )
            );
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('project_detail', ['projectId' => $projectId]);
    }
}
