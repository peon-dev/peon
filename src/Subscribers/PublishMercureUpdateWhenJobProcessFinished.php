<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Job\Event\JobProcessFinished;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobProcessFinished implements EventHandlerInterface
{
    public function __construct(
        private readonly HubInterface $hub,
        private readonly LoggerInterface $logger,
        private readonly Environment $twig,
    ) {}


    public function __invoke(JobProcessFinished $event): void
    {
        try {
            $this->hub->publish(
                new Update(
                    'job-' . $event->jobId->id . '-detail',
                    $this->twig->render('job/process_status_changed.stream.html.twig', [
                        'processId' => $event->processId->id,
                        'succeeded' => $event->succeed,
                        'failed' => $event->succeed === false,
                        'executionTime' => $event->executionTime,
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
