<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\Exception\InsufficientAccessToRemoteRepository;
use Peon\Domain\GitProvider\Value\GitRepositoryAuthentication;
use Peon\Domain\GitProvider\Exception\InvalidRemoteUri;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Ui\Form\CreateProjectFormData;
use Peon\Ui\Form\CreateProjectFormType;
use Peon\UseCase\CreateProject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CreateProjectController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {}


    #[Route(path: '/add-project', name: 'add_project')]
    public function __invoke(Request $request, UserId $userId): Response {
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
                            GitRepositoryAuthentication::fromPersonalAccessToken($data->personalAccessToken),
                        ),
                        $userId,
                    ),
                );

                return $this->redirectToRoute('dashboard');
            } catch (InvalidRemoteUri $invalidRemoteUri) {
                $form->get('remoteRepositoryUri')->addError(new FormError($invalidRemoteUri->getMessage()));
            } catch (InsufficientAccessToRemoteRepository) {
                $form->get('personalAccessToken')->addError(new FormError('Token does not have permission to open merge requests for the project!'));
            } catch (GitProviderCommunicationFailed $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        return $this->renderForm('add_project.html.twig', [
            'add_project_form' => $form,
        ]);
    }
}
