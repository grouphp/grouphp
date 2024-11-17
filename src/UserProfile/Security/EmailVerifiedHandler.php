<?php declare(strict_types=1);

namespace App\UserProfile\Security;

use App\UserProfile\Domain\UserProfile;
use App\UserProfile\Domain\UserProfileRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class EmailVerifiedHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UserProfileRepository $profiles,
        private ClockInterface        $clock,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface   $translator,
    ) {}

    #[\Override] public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var UserProfile $tokenUser */
        $tokenUser = $token->getUser();
        $profile = $this->profiles->load($tokenUser->id());
        $profile->verifyEmail($this->clock);
        $this->profiles->save($profile);

        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add(
            'success',
            $this->translator->trans('Email verified, you can now log in.'
        ));

        // We need to redirect here, since the account will be activated using
        // eventual consistency and is not available immediately.
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}