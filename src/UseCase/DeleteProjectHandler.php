<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\Project\Event\ProjectDeleted;
use PHPMate\Domain\Project\Exception\ProjectNotFound;
use PHPMate\Domain\Project\ProjectsCollection;
use PHPMate\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DeleteProjectHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private EventBus $eventBus,
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function __invoke(DeleteProject $command): void
    {
        $this->projectsCollection->remove($command->projectId);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new ProjectDeleted($command->projectId)
        );
    }
}
