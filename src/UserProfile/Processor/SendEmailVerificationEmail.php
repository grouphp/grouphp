<?php declare(strict_types=1);

namespace App\UserProfile\Processor;


use App\UserProfile\Domain\Event\RegistrationStarted;
use Patchlevel\EventSourcing\Attribute\Processor;
use Patchlevel\EventSourcing\Attribute\Subscribe;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

#[Processor('user_profile.send_verification_email')]
final readonly class SendEmailVerificationEmail
{
    public function __construct(
        private NotifierInterface $notifier,
    ){}

    #[Subscribe(RegistrationStarted::class)]
    public function __invoke(RegistrationStarted $registration): void
    {
        $this->notifier->send(
            (new Notification('New Invoice', ['email']))
                ->content('You got a new invoice for 15 EUR.'),
            new Recipient(email: $registration->email)
        );
    }
}