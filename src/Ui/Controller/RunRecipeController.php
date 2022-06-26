<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Cookbook\Exception\RecipeNotEnabled;
use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Job\Exception\JobExecutionFailed;
use Peon\Domain\Job\Exception\JobHasFinishedAlready;
use Peon\Domain\Job\Exception\JobHasNotStartedYet;
use Peon\Domain\Job\Exception\JobHasStartedAlready;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\RunRecipe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RunRecipeController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    /**
     * @throws JobExecutionFailed
     * @throws JobHasFinishedAlready
     * @throws JobHasNotStartedYet
     * @throws JobHasStartedAlready
     * @throws JobNotFound
     */
    #[Route(path: '/projects/{projectId}/run-recipe/{recipeName}', name: 'run_recipe')]
    public function __invoke(ProjectId $projectId, string $recipeName, UserId $userId): Response
    {
        try {
            $this->checkUserAccess->toProject($userId, $projectId);

            $this->commandBus->dispatch(
                new RunRecipe(
                    $projectId,
                    RecipeName::tryFrom($recipeName) ?? throw new RecipeNotFound(),
                )
            );

            return $this->redirectToRoute('project_overview', ['projectId' => $projectId]);
        } catch (ProjectNotFound | RecipeNotFound | RecipeNotEnabled | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }
    }
}
