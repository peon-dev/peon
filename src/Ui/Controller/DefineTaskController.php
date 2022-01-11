<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Task\Exception\InvalidCronExpression;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Ui\Form\DefineTaskFormData;
use Peon\Ui\Form\DefineTaskFormType;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Peon\UseCase\DefineTask;
use Peon\UseCase\DefineTaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DefineTaskController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
        private ProvideReadProjectDetail $provideReadProjectDetail,
    ) {}


    #[Route(path: '/define-task/{projectId}', name: 'define_task')]
    public function __invoke(string $projectId, Request $request): Response
    {
        try {
            $activeProject = $this->provideReadProjectDetail->provide(new ProjectId($projectId));
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(DefineTaskFormType::class);

        try {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var DefineTaskFormData $data */
                $data = $form->getData();

                $this->commandBus->dispatch(
                    new DefineTask(
                        new ProjectId($projectId),
                        $data->name,
                        $data->getCommandsAsArray(),
                        $data->getSchedule()
                    )
                );

                return $this->redirectToRoute('project_overview', ['projectId' => $projectId]);
            }
        } catch (InvalidCronExpression $invalidCronExpression) {
            $form->get('schedule')->addError(new FormError($invalidCronExpression->getMessage()));
        }

        return $this->renderForm('define_task.html.twig', [
            'define_task_form' => $form,
            'activeProject' => $activeProject,
        ]);
    }
}
