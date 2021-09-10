<?php

declare(strict_types=1);

namespace PHPMate\Ui\Controller;

use PHPMate\Domain\GitProvider\InsufficientAccessToRemoteRepository;
use PHPMate\Domain\Tools\Git\GitRepositoryAuthentication;
use PHPMate\Domain\Tools\Git\InvalidRemoteUri;
use PHPMate\Domain\Tools\Git\RemoteGitRepository;
use PHPMate\Packages\MessageBus\Command\CommandBus;
use PHPMate\Ui\Form\CreateProjectFormData;
use PHPMate\Ui\Form\CreateProjectFormType;
use PHPMate\UseCase\CreateProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CreateProjectController extends AbstractController
{
    public function __construct(
        private CommandBus $commandBus
    ) {}


    #[Route(path: '/create-project', name: 'create_project')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(CreateProjectFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateProjectFormData $data */
            $data = $form->getData();

            try {
                $this->commandBus->dispatch(
                    new CreateProject(
                        new RemoteGitRepository(
                            $data->remoteRepositoryUri,
                            GitRepositoryAuthentication::fromPersonalAccessToken($data->personalAccessToken)
                        )
                    )
                );

                return $this->redirectToRoute('dashboard');
            } catch (InvalidRemoteUri $invalidRemoteUri) {
                $form->get('remoteRepositoryUri')->addError(new FormError($invalidRemoteUri->getMessage()));
            } catch (InsufficientAccessToRemoteRepository) {
                $form->addError(new FormError('Could not access remote git repository! Please check your credentials.'));
            }
        }

        return $this->render('create_project.html.twig', [
            'create_project_form' => $form->createView(),
        ]);
    }
}
