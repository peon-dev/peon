<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Job\Event\JobProcessStarted;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Peon\Ui\ReadModel\Process\ReadProcess;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobProcessStarted implements EventHandlerInterface
{
    private bool $shouldSkipMercurePublishing = false;


    public function __construct(
        private readonly HubInterface $hub,
        private readonly LoggerInterface $logger,
        private readonly Environment $twig,
    ) {}


    public function __invoke(JobProcessStarted $event): void
    {
        // To prevent flooding in case of error
        if ($this->shouldSkipMercurePublishing === true) {
            try {
                $this->hub->publish(
                    new Update(
                        'job-' . $event->jobId->id . '-detail',
                        $this->twig->render('job/process_started.stream.html.twig', [
                            'process' => new ReadProcess(
                                $event->processId->id,
                                $event->jobId->id,
                                $event->command,
                                0, // TODO: this is here only to satisfy the constructor
                                null,
                                null,
                                null,
                            ),
                        ])
                    )
                );
            } catch (\Throwable $throwable) {
                $this->shouldSkipMercurePublishing = true;

                $this->logger->warning($throwable->getMessage(), [
                    'exception' => $throwable,
                ]);
            }
        }
    }
}
