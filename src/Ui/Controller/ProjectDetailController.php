<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProjectDetailController extends AbstractController
{
    #[Route(path: '/project/{projectId}', name: 'project_detail')]
    public function __invoke(string $projectId): Response
    {
        return new Response();
    }
}
