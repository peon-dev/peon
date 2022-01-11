<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Exception\InsufficientAccessToRemoteRepository;
use Peon\Domain\Project\Event\ProjectAdded;
use Peon\Domain\Project\Project;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Packages\MessageBus\Event\EventBus;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CreateProjectHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private CheckWriteAccessToRemoteRepository $checkWriteAccessToRemoteRepository,
        private EventBus $eventBus,
    ) {}

    /**
     * @throws InsufficientAccessToRemoteRepository
     * @throws GitProviderCommunicationFailed
     */
    public function __invoke(CreateProject $createProject): void
    {
        $remoteGitRepository = $createProject->remoteGitRepository;

        if (!$this->checkWriteAccessToRemoteRepository->hasWriteAccess($remoteGitRepository)) {
            throw new InsufficientAccessToRemoteRepository();
        }

        $project = new Project(
            $this->projectsCollection->nextIdentity(),
            $remoteGitRepository,
        );

        $this->projectsCollection->save($project);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new ProjectAdded($project->projectId)
        );
    }
}
