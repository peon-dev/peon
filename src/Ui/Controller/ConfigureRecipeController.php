<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Exception\CouldNotConfigureDisabledRecipe;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\ConfigureRecipeForProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ConfigureRecipeController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
    )
    {
    }


    #[Route(path: '/projects/{projectId}/configure-recipe/{recipeName}', name: 'configure_recipe')]
    public function __invoke(string $projectId, string $recipeName): Response
    {
        try {
            $recipeName = RecipeName::tryFrom($recipeName);

            if ($recipeName === null) {
                throw new RecipeNotFound();
            }

            $this->commandBus->dispatch(
                new ConfigureRecipeForProject(
                    new ProjectId($projectId),
                    $recipeName,
                    true,
                )
            );
        } catch (ProjectNotFound | RecipeNotFound | CouldNotConfigureDisabledRecipe) {
            $this->redirectToRoute('project_overview', ['projectId' => $projectId]);
        }

        return $this->renderForm('configure_recipe.html.twig', [

        ]);
    }
}
