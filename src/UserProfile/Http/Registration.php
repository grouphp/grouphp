<?php declare(strict_types=1);

namespace App\UserProfile\Http;

use App\UserProfile\Domain\UserProfile;
use App\UserProfile\Domain\UserProfileId;
use App\UserProfile\Domain\UserProfileRepository;
use App\UserProfile\Projector\Accounts;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('registration')]
#[AsController]
final class Registration extends AbstractController
{
    public function __invoke(
        Request                     $request,
        UserPasswordHasherInterface $hasher,
        UserProfileRepository       $profiles,
        Accounts                    $credentials,
        TranslatorInterface         $translator,
    ): Response
    {
        $form = $this->createForm(RegistrationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RegistrationData $data */
            $data = $form->getData();

            try {
                $credentials->findByEmail($data->email);
                $form->addError(new FormError($translator->trans('email_in_use')));
                goto render;
            } catch (UserNotFoundException) {
                $profile = UserProfile::startWithRegistration(
                    UserProfileId::generate(),
                    $data->email,
                    $data->password,
                    $hasher,
                );

                $profiles->save($profile);

                return $this->redirectToRoute('dashboard');
            }
        }

        render:
        return $this->render('register.html.twig', [
            'form' => $form,
        ]);
    }
}