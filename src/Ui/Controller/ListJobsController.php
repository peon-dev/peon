<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ListJobsController extends AbstractController
{
    #[Route(path: '/', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('jobs_list.html.twig', [
            'jobs' => [],
        ]);
    }
}
