<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Task\Exception\InvalidCronExpression;
use Peon\Domain\Task\Value\TaskId;
use Peon\Domain\Task\Exception\TaskNotFound;
use Peon\Domain\Task\TasksCollection;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Ui\Form\DefineTaskFormData;
use Peon\Ui\Form\DefineTaskFormType;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Peon\UseCase\RedefineTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RedefineTaskController extends AbstractController
{
    public function __construct(
        private TasksCollection $tasks,
        private CommandBus $commandBus,
        private ProvideReadProjectDetail $provideReadProjectDetail,
    ) {}


    #[Route(path: '/redefine-task/{taskId}', name: 'redefine_task')]
    public function __invoke(string $taskId, Request $request): Response
    {
        try {
            $task = $this->tasks->get(new TaskId($taskId));
            $project = $this->provideReadProjectDetail->provide($task->projectId);
            $form = $this->createForm(DefineTaskFormType::class, DefineTaskFormData::fromTask($task));

            try {
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    /** @var DefineTaskFormData $data */
                    $data = $form->getData();

                    $this->commandBus->dispatch(
                        new RedefineTask(
                            $task->taskId,
                            $data->name,
                            $data->getCommandsAsArray(),
                            $data->getSchedule()
                        )
                    );

                    return $this->redirectToRoute('project_overview', ['projectId' => $task->projectId]);
                }
            } catch (InvalidCronExpression $invalidCronExpression) { // TODO this could be handled better way by custom validation rule
                $form->get('schedule')->addError(new FormError($invalidCronExpression->getMessage()));
            }
        } catch (TaskNotFound | ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->renderForm('redefine_task.html.twig', [
            'task' => $task,
            'define_task_form' => $form,
            'activeProject' => $project,
        ]);
    }
}
