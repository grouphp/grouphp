<?php declare(strict_types=1);

namespace App\UserProfile\Processor;


use App\UserProfile\Domain\Event\RegistrationStarted;
use Patchlevel\EventSourcing\Attribute\Processor;
use Patchlevel\EventSourcing\Attribute\Subscribe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[Processor('user_profile.send_verification_email')]
final readonly class SendEmailVerificationEmail
{
    public function __construct(
        private MailerInterface $mailer
    ){}

    /**
     * @throws TransportExceptionInterface
     */
    #[Subscribe(RegistrationStarted::class)]
    public function __invoke(RegistrationStarted $registration): void
    {
        $this->mailer
            ->send((new TemplatedEmail())
            ->to(new Address($registration->email))
            ->subject('Verify your email address'));
    }
}