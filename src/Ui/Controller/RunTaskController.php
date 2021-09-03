<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\UseCase\RunTask;
use PHPMate\UseCase\RunTaskCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RunTaskController extends AbstractController
{
    public function __construct(
        private RunTask $runTaskUseCase
    ) {}


    #[Route(path: '/task/run/{taskId}', name: 'run_task')]
    public function __invoke(string $taskId): Response
    {
        try {
            $this->runTaskUseCase->handle(
                new RunTaskCommand(
                    new TaskId($taskId)
                )
            );
        } catch (TaskNotFound | ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('dashboard');
    }
}
