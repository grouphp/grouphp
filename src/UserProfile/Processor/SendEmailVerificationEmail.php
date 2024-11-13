<?php declare(strict_types=1);

namespace App\UserProfile\Processor;


use App\UserProfile\Domain\Event\RegistrationStarted;
use App\UserProfile\Domain\UserProfileRepository;
use Patchlevel\EventSourcing\Attribute\Processor;
use Patchlevel\EventSourcing\Attribute\Subscribe;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Processor('user_profile.send_verification_email')]
final readonly class SendEmailVerificationEmail
{
    public function __construct(
        private NotifierInterface $notifier,
        private TranslatorInterface $translator,
        private LocaleSwitcher $localeSwitcher,
        #[Autowire(service: 'security.authenticator.login_link_handler.main')]
        private LoginLinkHandlerInterface $loginLinkHandler,
        private UserProfileRepository $profiles,
    ){}

    #[Subscribe(RegistrationStarted::class)]
    public function __invoke(RegistrationStarted $registration): void
    {
        $profile = $this->profiles->load($registration->id);

        $this->localeSwitcher->runWithLocale('en', function() use ($registration, $profile): void {
            $link = $this->loginLinkHandler->createLoginLink($profile);

            $notification = new LoginLinkNotification(
                $link,
                $this->translator->trans('Welcome')
            );

            $this->notifier->send(
                $notification,
                new Recipient(email: $registration->email)
            );
        });
    }
}