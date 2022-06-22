<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RemoveTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class RemoveTaskController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly TasksCollection $tasksCollection,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    #[Route(path: '/remove-task/{taskId}', name: 'remove_task')]
    public function __invoke(string $taskId, UserInterface $user): Response
    {
        $userId = new UserId($user->getUserIdentifier());

        try {
            $task = $this->tasksCollection->get(new TaskId($taskId));

            $this->checkUserAccess->toProject($userId, $task->projectId);

            $this->commandBus->dispatch(
                new RemoveTask(
                    new TaskId($taskId)
                )
            );

            return $this->redirectToRoute('project_overview', ['projectId' => $task->projectId]);
        } catch (TaskNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }
    }
}
