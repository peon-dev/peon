<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Cookbook\Exception\RecipeNotEnabled;
use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Ui\Form\ConfigureRecipeFormData;
use Peon\Ui\Form\ConfigureRecipeFormType;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Peon\UseCase\ConfigureRecipeForProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ConfigureRecipeController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
        private ProvideReadProjectDetail $provideReadProjectDetail,
        private RecipesCollection $recipesCollection,
    ) {
    }


    #[Route(path: '/projects/{projectId}/configure-recipe/{recipeName}', name: 'configure_recipe')]
    public function __invoke(string $projectId, string $recipeName, Request $request): Response
    {
        try {
            if (RecipeName::tryFrom($recipeName) === null) {
                throw new RecipeNotFound();
            }

            $project = $this->provideReadProjectDetail->provide(new ProjectId($projectId));
            $configureRecipeForm = $this->createForm(ConfigureRecipeFormType::class, ConfigureRecipeFormData::fromReadProjectDetail(
                $project,
                RecipeName::from($recipeName),
            ));

            $configureRecipeForm->handleRequest($request);

            if ($configureRecipeForm->isSubmitted() && $configureRecipeForm->isValid()) {
                $data = $configureRecipeForm->getData();
                assert($data instanceof ConfigureRecipeFormData);

                $this->commandBus->dispatch(
                    new ConfigureRecipeForProject(
                        new ProjectId($projectId),
                        RecipeName::from($recipeName),
                        $data->mergeAutomatically,
                    )
                );

                return $this->redirectToRoute('project_overview', ['projectId' => $projectId]);
            }

            return $this->renderForm('configure_recipe.html.twig', [
                'activeProject' => $project,
                'configureRecipeForm' => $configureRecipeForm,
                'recipe' => $this->recipesCollection->get(RecipeName::from($recipeName)),
            ]);
        } catch (RecipeNotFound | RecipeNotEnabled) {
            return $this->redirectToRoute('project_overview', ['projectId' => $projectId]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
