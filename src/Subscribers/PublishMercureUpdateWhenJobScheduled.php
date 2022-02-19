<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Job\Event\JobScheduled;
use Peon\Domain\Job\Exception\JobNotFound;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Peon\Ui\ReadModel\Dashboard\ProvideReadProjectById;
use Peon\Ui\ReadModel\Job\ProvideReadJobById;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobScheduled implements EventHandlerInterface
{
    public function __construct(
        private Environment $twig,
        private HubInterface $hub,
        private ProvideReadJobById $provideReadJobById,
        private ProvideReadProjectById $provideReadProjectById,
    ) {}


    /**
     * @throws JobNotFound
     * @throws ProjectNotFound
     *
     */
    public function __invoke(JobScheduled $event): void
    {
        $job = $this->provideReadJobById->provide($event->jobId);
        $project = $this->provideReadProjectById->provide($event->projectId);

        $this->hub->publish(
            new Update(
                'project-' . $event->projectId->id . '-overview',
                $this->twig->render('project_overview.stream.html.twig', [
                    'job' => $job,
                    'isFirstJob' => false, // TODO: really count jobs
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
    }
}
