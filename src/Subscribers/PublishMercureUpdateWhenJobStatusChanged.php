<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Job\Event\JobStatusChanged;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Peon\Ui\ReadModel\Job\ProvideReadJobById;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobStatusChanged implements EventHandlerInterface
{
    public function __construct(
        private readonly HubInterface $hub,
        private readonly Environment $twig,
        private readonly ProvideReadJobById $provideReadJobById,
        private readonly LoggerInterface $logger,
    ) {}


    public function __invoke(JobStatusChanged $event): void
    {
        try {
            $job = $this->provideReadJobById->provide($event->jobId);

            $this->hub->publish(
                new Update(
                    'project-' . $event->projectId->id . '-overview',
                    $this->twig->render('job_status_changed.stream.html.twig', [
                        'job' => $job,
                    ])
                )
            );

            $this->hub->publish(
                new Update(
                    'dashboard',
                    $this->twig->render('dashboard.job_status_changed.stream.html.twig', [
                        'job' => $job,
                    ])
                )
            );

            $this->hub->publish(
                new Update(
                    'event-stream',
                    $this->twig->render('job/job_status_changed.stream.html.twig', [
                        'status' => $job->status,
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
