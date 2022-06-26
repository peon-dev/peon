<?php

declare(strict_types=1);

namespace Peon\Ui\Controller;

use Peon\Domain\User\Value\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    public function __construct(
        private readonly AuthenticationUtils $authenticationUtils,
    )
    {
    }


    #[Route('/login', name: 'login')]
    public function __invoke(null|UserId $userId): Response
    {
        if ($userId !== null) {
            return $this->redirectToRoute('dashboard');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }
}
