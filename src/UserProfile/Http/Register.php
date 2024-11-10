<?php declare(strict_types=1);

namespace App\UserProfile\Http;

use App\UserProfile\Domain\UserProfile;
use App\UserProfile\Domain\UserProfileId;
use App\UserProfile\Domain\UserProfileRepository;
use App\UserProfile\Form\RegistrationData;
use App\UserProfile\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('register')]
#[AsController]
final class Register extends AbstractController
{
    public function __invoke(
        Request $request,
        UserPasswordHasherInterface $hasher,
        UserProfileRepository $profiles,
    ): Response
    {
        $form = $this->createForm(RegistrationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RegistrationData $data */
            $data = $form->getData();

            // TODO: Check if email address already exists.

            $profile = UserProfile::startWithRegistration(
                UserProfileId::generate(),
                $data->email,
                $data->password,
                $hasher,
            );

            $profiles->save($profile);
        }

        return $this->render('register.html.twig', [
            'form' => $form,
        ]);
    }
}