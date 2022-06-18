<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Ui\ReadModel\Dashboard\ProvideReadJobs;
use Peon\Ui\ReadModel\Dashboard\ProvideReadProjects;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly ProvideReadJobs $provideReadJobs,
        private readonly ProvideReadProjects $provideReadProjects,
    ) {}


    #[Route(path: '/', name: 'dashboard', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('dashboard.html.twig', [
            'jobs' => $this->provideReadJobs->provide(10),
            'projects' => $this->provideReadProjects->provide(),
        ]);
    }
}
