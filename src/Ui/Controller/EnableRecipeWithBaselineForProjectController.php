<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Cookbook\Exception\RecipeNotFound;
use Peon\Domain\Cookbook\Value\RecipeName;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\UseCase\EnableRecipeWithBaselineForProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class EnableRecipeWithBaselineForProjectController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    /**
     * @throws GitProviderCommunicationFailed
     */
    #[Route(path: '/projects/{projectId}/recipe/{recipeName}/enable-with-baseline', name: 'project_enable_recipe_with_baseline')]
    public function __invoke(string $projectId, string $recipeName, UserInterface $user): Response
    {
        $userId = new UserId($user->getUserIdentifier());

        try {
            $this->checkUserAccess->toProject($userId, new ProjectId($projectId));

            $this->commandBus->dispatch(
                new EnableRecipeWithBaselineForProject(
                    RecipeName::tryFrom($recipeName) ?? throw new RecipeNotFound(),
                    new ProjectId($projectId)
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
