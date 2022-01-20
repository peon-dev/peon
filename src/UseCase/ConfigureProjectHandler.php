<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;

final class ConfigureProjectHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function __invoke(ConfigureProject $command): void
    {
       $project = $this->projectsCollection->get($command->projectId);
       $project->configureBuild($command->buildConfiguration);

       $this->projectsCollection->save($project);
    }
}
