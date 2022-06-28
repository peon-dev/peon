<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Job\Event\JobScheduled;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Peon\Ui\ReadModel\Dashboard\ProvideReadProjectById;
use Peon\Ui\ReadModel\Job\CountJobsOfProject;
use Peon\Ui\ReadModel\Job\ProvideReadJobById;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobScheduled implements EventHandlerInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly HubInterface $hub,
        private readonly ProvideReadJobById $provideReadJobById,
        private readonly ProvideReadProjectById $provideReadProjectById,
        private readonly CountJobsOfProject $countJobsOfProject,
        private readonly LoggerInterface $logger,
    ) {}


    public function __invoke(JobScheduled $event): void
    {
        try {
            $job = $this->provideReadJobById->provide($event->jobId);
            $project = $this->provideReadProjectById->provide($event->projectId);
            $jobsCount = $this->countJobsOfProject->count($event->projectId);

            $this->hub->publish(
                new Update(
                    'project-' . $event->projectId->id . '-overview',
                    $this->twig->render('project_overview.stream.html.twig', [
                        'job' => $job,
                        'isFirstJob' => $jobsCount === 1,
                    ])
                )
            );

            $this->hub->publish(
                new Update(
                    'dashboard',
                    $this->twig->render('dashboard.stream.html.twig', [
                        'job' => $job,
                        'project' => $project,
                    ])
                )
            );
        } catch (\Throwable $throwable) {
            $this->logger->warning($throwable->getMessage(), [
                'exception' => $throwable,
            ]);
        }
    }
}
