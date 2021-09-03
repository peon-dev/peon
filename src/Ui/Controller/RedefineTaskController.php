<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\ProjectId;
use PHPMate\Domain\Project\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Task\TaskId;
use PHPMate\Domain\Task\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Ui\Form\DefineTaskFormData;
use PHPMate\Ui\Form\DefineTaskFormType;
use PHPMate\UseCase\DefineTaskCommand;
use PHPMate\UseCase\DefineTaskUseCase;
use PHPMate\UseCase\RedefineTaskCommand;
use PHPMate\UseCase\RedefineTaskUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RedefineTaskController extends AbstractController
{
    public function __construct(
        private TasksCollection $tasks,
        private RedefineTaskUseCase $redefineTaskUseCase
    ) {}


    #[Route(path: '/redefine-task/{taskId}', name: 'redefine_task')]
    public function __invoke(string $taskId, Request $request): Response
    {
        try {
            $task = $this->tasks->get(new TaskId($taskId));
        } catch (TaskNotFound) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(DefineTaskFormType::class, DefineTaskFormData::fromTask($task));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DefineTaskFormData $data */
            $data = $form->getData();

            $this->redefineTaskUseCase->handle(
                new RedefineTaskCommand(
                    $task->taskId,
                    $data->name,
                    $data->getCommandsAsArray()
                )
            );

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('redefine_task.html.twig', [
            'task' => $task,
            'define_task_form' => $form->createView(),
        ]);
    }
}
