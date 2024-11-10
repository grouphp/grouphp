<?php declare(strict_types=1);

namespace App\UserProfile\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('login')]
#[AsController]
final class Login extends AbstractController
{
    public function __invoke(
        Request $request,
    ): Response
    {
        $form = $this->createForm(LoginType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var LoginData $data */
            $data = $form->getData();
        }

        return $this->render('register.html.twig', [
            'form' => $form,
        ]);
    }
}