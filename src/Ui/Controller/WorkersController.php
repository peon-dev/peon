<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Ui\ReadModel\Worker\CountScheduledJobs;
use Peon\Ui\ReadModel\Worker\ProvideActiveReadWorkers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class WorkersController extends AbstractController
{
    public function __construct(
        private readonly CountScheduledJobs $countQueuedJobs,
        private readonly ProvideActiveReadWorkers $provideActiveReadWorkers,
    ) {}


    #[Route(path: '/workers', name: 'workers')]
    public function __invoke(): Response
    {
        return $this->render('workers.html.twig', [
            'workers' => $this->provideActiveReadWorkers->provide(),
            'queued_jobs_count' => $this->countQueuedJobs->count(),
        ]);
    }
}
