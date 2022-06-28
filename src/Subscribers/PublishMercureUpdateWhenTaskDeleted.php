<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Task\Event\TaskDeleted;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Peon\Ui\ReadModel\Dashboard\ProvideReadProjectById;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenTaskDeleted implements EventHandlerInterface
{
    public function __construct(
        private readonly HubInterface $hub,
        private readonly Environment $twig,
        private readonly ProvideReadProjectById $provideReadProjectById,
        private readonly LoggerInterface $logger,
    ) {}


    public function __invoke(TaskDeleted $event): void
    {
        // TODO: Project overview - task row

        try {
            $project = $this->provideReadProjectById->provide($event->projectId);

            $this->hub->publish(
                new Update(
                    'dashboard',
                    $this->twig->render('dashboard.project_stats.stream.html.twig', [
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
