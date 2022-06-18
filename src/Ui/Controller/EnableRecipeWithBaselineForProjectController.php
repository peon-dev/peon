<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\EnableRecipeWithBaselineForProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class EnableRecipeWithBaselineForProjectController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}


    /**
     * @throws GitProviderCommunicationFailed
     */
    #[Route(path: '/projects/{projectId}/recipe/{recipeName}/enable-with-baseline', name: 'project_enable_recipe_with_baseline')]
    public function __invoke(string $projectId, string $recipeName): Response
    {
        try {
            $this->commandBus->dispatch(
                new EnableRecipeWithBaselineForProject(
                    RecipeName::tryFrom($recipeName) ?? throw new RecipeNotFound(),
                    new ProjectId($projectId)
                )
            );
        } catch (ProjectNotFound | RecipeNotFound) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('cookbook', [
            'projectId' => $projectId,
        ]);
    }
}
