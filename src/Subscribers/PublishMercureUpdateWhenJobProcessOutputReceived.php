<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Job\Event\JobProcessOutputReceived;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobProcessOutputReceived implements EventHandlerInterface
{
    public function __construct(
        private readonly HubInterface $hub,
        private readonly LoggerInterface $logger,
        private readonly Environment $twig,
        private bool $shouldSkipMercurePublishing = false,
    ) {}


    public function __invoke(JobProcessOutputReceived $event): void
    {
        // To prevent spamming when mercure is off for example
        if ($this->shouldSkipMercurePublishing === false) {
            try {
                $this->hub->publish(
                    new Update(
                        'job-' . $event->jobId->id . '-detail',
                        $this->twig->render('job/process_output_buffer.stream.html.twig', [
                            'buffer' => $event->outputBuffer,
                            'processId' => $event->processId->id,
                        ])
                    )
                );
            } catch (\Throwable $throwable) {
                $this->logger->warning($throwable->getMessage(), [
                    'exception' => $throwable,
                ]);

                $this->shouldSkipMercurePublishing = true;
            }
        }
    }
}
