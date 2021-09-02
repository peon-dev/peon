<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Task\TaskId;
use PHPMate\UseCase\RemoveTask;
use PHPMate\UseCase\RemoveTaskUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RemoveTaskController extends AbstractController
{
    public function __construct(
        private RemoveTaskUseCase $removeTaskUseCase
    ) {}


    #[Route(path: '/remove-task/{taskId}', name: 'remove_task')]
    public function __invoke(string $taskId): Response
    {
        $this->removeTaskUseCase->handle(
            new RemoveTask(
                new TaskId($taskId)
            )
        );

        return $this->redirectToRoute('jobs_list');
    }
}
