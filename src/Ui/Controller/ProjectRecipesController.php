<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\Ui\Form\ProjectRecipesFormData;
use PHPMate\Ui\Form\ProjectRecipesFormType;
use PHPMate\UseCase\ChangeProjectRecipes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectRecipesController extends AbstractController
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private CommandBus $commandBus
    ) {}


    #[Route(path: '/project/{projectId}/recipes', name: 'project_recipes')]
    public function __invoke(string $projectId, Request $request): Response
    {
        try {
            $project = $this->projectsCollection->get(new ProjectId($projectId));
            $form = $this->createForm(ProjectRecipesFormType::class, ProjectRecipesFormData::fromProject($project));

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var ProjectRecipesFormData $data */
                $data = $form->getData();

                $this->commandBus->dispatch(
                    new ChangeProjectRecipes(
                        $project->projectId,
                        $data->getRecipeNames()
                    )
                );

                return $this->redirectToRoute('project_detail', ['projectId' => $projectId]);
            }
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->render('project_recipes.html.twig', [
            'project_recipes_form' => $form->createView(),
            'activeProject' => $project,
        ]);
    }
}
