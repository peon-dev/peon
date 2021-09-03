<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RunTaskController extends AbstractController
{
    #[Route(path: '/task/run/{taskId}', name: 'run_task')]
    public function __invoke(): Response
    {
        return $this->redirectToRoute('dashboard');
    }
}
