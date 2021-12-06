<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DisableRecipeForProjectController extends AbstractController
{
    #[Route(path: '/projects/{projectId}/recipe/{recipeName}/disable', name: 'project_disable_recipe')]
    public function __invoke(string $projectId, string $recipeName): Response
    {
        return $this->redirectToRoute('cookbook', [
            'projectId' => $projectId,
        ]);
    }
}
