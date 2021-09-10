<?php

declare(strict_types=1);

namespace PHPMate\UseCase;

use PHPMate\Domain\GitProvider\CheckWriteAccessToRemoteRepository;
use PHPMate\Domain\GitProvider\GitProvider;
use PHPMate\Domain\GitProvider\InsufficientAccessToRemoteRepository;
use PHPMate\Domain\Project\Project;
use PHPMate\Domain\Project\ProjectsCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CreateProjectHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private CheckWriteAccessToRemoteRepository $checkWriteAccessToRemoteRepository
    ) {}

    /**
     * @throws InsufficientAccessToRemoteRepository
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
    }
}
