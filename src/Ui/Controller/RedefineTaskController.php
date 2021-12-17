<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Task\Exception\InvalidCronExpression;
use PHPMate\Domain\Task\Value\TaskId;
use PHPMate\Domain\Task\Exception\TaskNotFound;
use PHPMate\Domain\Task\TasksCollection;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\Ui\Form\DefineTaskFormData;
use PHPMate\Ui\Form\DefineTaskFormType;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use PHPMate\UseCase\RedefineTask;
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


    /**
     * @throws \Nette\Utils\JsonException
     */
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

        return $this->render('redefine_task.html.twig', [
            'task' => $task,
            'define_task_form' => $form->createView(),
            'activeProject' => $project,
        ]);
    }
}
