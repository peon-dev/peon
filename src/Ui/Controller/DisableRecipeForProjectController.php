<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Cookbook\Exception\RecipeNotFound;
use PHPMate\Domain\Cookbook\Value\RecipeName;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\Exception\RecipeNotEnabledForProject;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Packages\Enum\InvalidEnumValue;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\UseCase\DisableRecipeForProject;
use PHPMate\UseCase\EnableRecipeForProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DisableRecipeForProjectController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus
    ) {}


    #[Route(path: '/projects/{projectId}/recipe/{recipeName}/disable', name: 'project_disable_recipe')]
    public function __invoke(string $projectId, string $recipeName): Response
    {
        try {
            $this->commandBus->dispatch(
                new DisableRecipeForProject(
                    RecipeName::fromString($recipeName),
                    new ProjectId($projectId)
                )
            );
        } catch (ProjectNotFound | InvalidEnumValue | RecipeNotFound) {
            throw $this->createNotFoundException();
        } catch (RecipeNotEnabledForProject) {
            // Do nothing
        }

        return $this->redirectToRoute('cookbook', [
            'projectId' => $projectId,
        ]);
    }
}
