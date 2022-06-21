<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Domain\Security\CheckUserAccess;
use Peon\Domain\Security\Exception\ForbiddenUserAccessToProject;
use Peon\Domain\User\Value\UserId;
use Peon\Ui\ReadModel\Dashboard\ProvideProjectReadJobs;
use Peon\Ui\ReadModel\ProjectDetail\ProvideProjectReadRecipes;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadProjectDetail;
use Peon\Ui\ReadModel\ProjectDetail\ProvideReadTasks;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProjectOverviewController extends AbstractController
{
    public function __construct(
        private readonly ProvideReadProjectDetail  $provideReadProjectDetail,
        private readonly ProvideReadTasks          $provideReadTasks,
        private readonly ProvideProjectReadJobs    $provideProjectReadJobs,
        private readonly ProvideProjectReadRecipes $provideProjectReadRecipes,
        private readonly CheckUserAccess           $checkUserAccess,
    ) {}


    #[Route(path: '/projects/{projectId}', name: 'project_overview')]
    public function __invoke(string $projectId, UserInterface $user): Response
    {
        $id = new ProjectId($projectId);
        $userId = new UserId($user->getUserIdentifier());

        try {
            $this->checkUserAccess->toProject($userId,  $id);

            $project = $this->provideReadProjectDetail->provide($id);

            return $this->render('project_overview.html.twig', [
                'activeProject' => $project,
                'tasks' => $this->provideReadTasks->provide($id),
                'jobs' => $this->provideProjectReadJobs->provide($id, 20),
                'recipes' => $this->provideProjectReadRecipes->provide($id),
            ]);
        } catch (ProjectNotFound | ForbiddenUserAccessToProject) {
            throw $this->createNotFoundException();
        }
    }
}
