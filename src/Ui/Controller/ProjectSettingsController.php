<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\PhpApplication\Value\BuildConfiguration;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Ui\Form\ConfigureBuildFormData;
use Peon\Ui\Form\ConfigureBuildFormType;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Peon\UseCase\ConfigureProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectSettingsController extends AbstractController
{
    public function __construct(
        private ProvideReadProjectDetail $provideReadProjectDetail,
        private CommandBus $commandBus,
    ) {}


    #[Route(path: '/projects/{projectId}/settings', name: 'project_settings')]
    public function __invoke(string $projectId, Request $request): Response
    {
        try {
            $project = $this->provideReadProjectDetail->provide(new ProjectId($projectId));
            $configureBuildForm = $this->createForm(ConfigureBuildFormType::class, ConfigureBuildFormData::fromReadProjectDetail($project));

            $configureBuildForm->handleRequest($request);

            if ($configureBuildForm->isSubmitted() && $configureBuildForm->isValid()) {
                /** @var ConfigureBuildFormData $data */
                $data = $configureBuildForm->getData();

                $this->commandBus->dispatch(
                    new ConfigureProject(
                        new ProjectId($project->projectId),
                        new BuildConfiguration($data->skipComposerInstall)
                    )
                );

                return $this->redirectToRoute('project_overview', ['projectId' => $projectId]);
            }

            return $this->renderForm('project_settings.html.twig', [
                'activeProject' => $project,
                'configure_build_form' => $configureBuildForm,
            ]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
