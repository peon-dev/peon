<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Cookbook\Exception\RecipeNotFound;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Job\Exception\JobExecutionFailed;
use PHPMate\Domain\Job\Exception\JobHasFinishedAlready;
use PHPMate\Domain\Job\Exception\JobHasNoCommands;
use PHPMate\Domain\Job\Exception\JobHasNotStartedYet;
use PHPMate\Domain\Job\Exception\JobHasStartedAlready;
use PHPMate\Domain\Job\Exception\JobNotFound;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\UseCase\RunRecipe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RunRecipeController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus,
    ) {}


    /**
     * @throws JobExecutionFailed
     * @throws JobHasFinishedAlready
     * @throws JobHasNotStartedYet
     * @throws JobHasStartedAlready
     * @throws JobHasNoCommands
     * @throws JobNotFound
     */
    #[Route(path: '/projects/{projectId}/run-recipe/{recipeName}', name: 'run_recipe')]
    public function __invoke(string $projectId, string $recipeName): Response
    {
        try {
            $this->commandBus->dispatch(
                new RunRecipe(
                    new ProjectId($projectId),
                    RecipeName::tryFrom($recipeName) ?? throw new RecipeNotFound(),
                )
            );

            return $this->redirectToRoute('project_overview', ['projectId' => $projectId]);
        } catch (ProjectNotFound | RecipeNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
