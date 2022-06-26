<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Job\JobsCollection;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\Task\Exception\InvalidCronExpression;
use Peon\Domain\Task\TasksCollection;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Ui\Form\DefineTaskFormData;
use Peon\Ui\Form\DefineTaskFormType;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Peon\UseCase\DefineTask;
use Peon\UseCase\RunTask;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class DefineTaskController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly ProvideReadProjectDetail $provideReadProjectDetail,
        private readonly TasksCollection $tasksCollection,
        private readonly JobsCollection $jobsCollection,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    #[Route(path: '/define-task/{projectId}', name: 'define_task')]
    public function __invoke(ProjectId $projectId, Request $request, UserInterface $user): Response
    {
        $userId = new UserId($user->getUserIdentifier());

        try {
            $this->checkUserAccess->toProject($userId, $projectId);

            $activeProject = $this->provideReadProjectDetail->provide($projectId);
        } catch (ProjectNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(DefineTaskFormType::class);
        assert($form instanceof Form);

        try {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var DefineTaskFormData $data */
                $data = $form->getData();
                $taskId = $this->tasksCollection->nextIdentity();

                $this->commandBus->dispatch(
                    new DefineTask(
                        $taskId,
                        $projectId,
                        $data->name,
                        $data->getCommandsAsArray(),
                        $data->getSchedule(),
                        $data->mergeAutomatically,
                    )
                );

                if ($form->get('saveAndRun') === $form->getClickedButton()) {
                    $jobId = $this->jobsCollection->nextIdentity();

                    $this->commandBus->dispatch(
                        new RunTask($taskId, $jobId),
                    );

                    return $this->redirectToRoute('job_detail', ['jobId' => $jobId]);
                }

                return $this->redirectToRoute('project_overview', ['projectId' => $projectId]);
            }
        } catch (InvalidCronExpression $invalidCronExpression) {
            $form->get('schedule')->addError(new FormError($invalidCronExpression->getMessage()));
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->renderForm('define_task.html.twig', [
            'define_task_form' => $form,
            'activeProject' => $activeProject,
        ]);
    }
}
