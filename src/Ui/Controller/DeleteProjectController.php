<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\DeleteProject;
use Peon\UseCase\DeleteProjectHandler;
use Peon\UseCase\RemoveTask;
use Peon\UseCase\RemoveTaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class DeleteProjectController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    #[Route(path: '/delete-project/{projectId}', name: 'delete_project')]
    public function __invoke(string $projectId, UserInterface $user): Response
    {
        $userId = new UserId($user->getUserIdentifier());

        try {
            $this->checkUserAccess->toProject($userId, new ProjectId($projectId));

            $this->commandBus->dispatch(
                new DeleteProject(
                    new ProjectId($projectId)
                )
            );
        } catch (ProjectNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('dashboard');
    }
}
