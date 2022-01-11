<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Task\Value\TaskId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\DeleteProject;
use Peon\UseCase\DeleteProjectHandler;
use Peon\UseCase\RemoveTask;
use Peon\UseCase\RemoveTaskHandler;
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

        return $this->redirectToRoute('dashboard');
    }
}
