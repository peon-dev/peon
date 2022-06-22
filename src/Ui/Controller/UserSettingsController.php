<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\User\Exception\UserNotFound;
use Peon\Domain\User\Value\UserId;
use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Ui\Form\ChangePasswordFormData;
use Peon\Ui\Form\ChangePasswordFormType;
use Peon\UseCase\ChangeUserPassword;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserSettingsController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {}


    #[Route(path: '/user-settings', name: 'user_settings')]
    public function __invoke(Request $request, UserInterface $user): Response
    {
        $changePasswordForm = $this->createForm(ChangePasswordFormType::class);

        $changePasswordForm->handleRequest($request);

        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $data = $changePasswordForm->getData();
            assert($data instanceof ChangePasswordFormData);
            assert($user instanceof PasswordAuthenticatedUserInterface);

            // Check user knows his original password
            if ($this->userPasswordHasher->isPasswordValid($user, $data->oldPassword) === false) {
                $changePasswordForm->get('oldPassword')->addError(
                    new FormError('This is not your current password!'),
                );
            } else {
                try {
                    $this->commandBus->dispatch(
                        new ChangeUserPassword(
                            new UserId($user->getUserIdentifier()),
                            $data->newPassword,
                        )
                    );
                } catch (UserNotFound) {
                    return $this->redirectToRoute('dashboard');
                }

                $this->addFlash(
                    'success',
                    'Your password was changed!'
                );

                return $this->redirectToRoute('dashboard');
            }
        }

        return $this->renderForm('user_settings.html.twig', [
            'change_password_form' => $changePasswordForm,
        ]);
    }
}
