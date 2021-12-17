<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\Cookbook\RecipesCollection;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\Value\ProjectId;
use PHPMate\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CookbookController extends AbstractController
{
    public function __construct(
        private RecipesCollection $recipesCollection,
        private ProvideReadProjectDetail $provideReadProjectDetail,
    ) {}


    /**
     * @throws \Nette\Utils\JsonException
     */
    #[Route(path: '/projects/{projectId}/cookbook', name: 'cookbook')]
    public function __invoke(string $projectId): Response
    {
        try {
            $project = $this->provideReadProjectDetail->provide(new ProjectId($projectId));

            return $this->render('cookbook.html.twig', [
                'recipes' => $this->recipesCollection->all(),
                'activeProject' => $project,
            ]);
        } catch (ProjectNotFound) {
            throw $this->createNotFoundException();
        }
    }
}
