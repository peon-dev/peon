<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\EnableRecipeForProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class EnableRecipeForProjectController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    #[Route(path: '/projects/{projectId}/recipe/{recipeName}/enable', name: 'project_enable_recipe')]
    public function __invoke(ProjectId $projectId, string $recipeName, UserId $userId): Response
    {
        try {
            $this->checkUserAccess->toProject($userId, $projectId);

            $this->commandBus->dispatch(
                new EnableRecipeForProject(
                    RecipeName::tryFrom($recipeName) ?? throw new RecipeNotFound(),
                    $projectId
                )
            );
        } catch (ProjectNotFound | RecipeNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('cookbook', [
            'projectId' => $projectId,
        ]);
    }
}
