<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Cookbook\RecipesCollection;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\User\Value\UserId;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CookbookController extends AbstractController
{
    public function __construct(
        private readonly RecipesCollection $recipesCollection,
        private readonly ProvideReadProjectDetail $provideReadProjectDetail,
        private readonly CheckUserAccess $checkUserAccess,
    ) {}


    #[Route(path: '/projects/{projectId}/cookbook', name: 'cookbook')]
    public function __invoke(ProjectId $projectId, UserId $userId): Response
    {
        try {
            $this->checkUserAccess->toProject($userId,  $projectId);

            $project = $this->provideReadProjectDetail->provide($projectId);

            return $this->render('cookbook.html.twig', [
                'recipes' => $this->recipesCollection->all(),
                'activeProject' => $project,
            ]);
        } catch (ProjectNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }
    }
}
