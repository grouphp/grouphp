<?php declare(strict_types=1);

namespace App\UserProfile\Http;

use App\Controller\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('login', name: 'app_login')]
#[AsController]
final class Login extends AbstractController
{
    public function __invoke(
        ?UserInterface $user,
        AuthenticationUtils $authenticationUtils,
    ): Response
    {
        if ($user && $this->isGranted('ROLE_USER')) {
            // TODO: improve that one to show a message
            //       and allow the user to go to the dashboard or login
            //       with a different account
            return $this->redirectToRoute(Dashboard::class);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}