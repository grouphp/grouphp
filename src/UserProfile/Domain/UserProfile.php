<?php declare(strict_types=1);

namespace App\UserProfile\Domain;

use App\UserProfile\Domain\Event\EmailVerified;
use App\UserProfile\Domain\Event\RegistrationStarted;
use App\UserProfile\Projector\Accounts;
use Patchlevel\EventSourcing\Aggregate\BasicAggregateRoot;
use Patchlevel\EventSourcing\Attribute\Aggregate;
use Patchlevel\EventSourcing\Attribute\Apply;
use Patchlevel\EventSourcing\Attribute\Id;
use Patchlevel\Hydrator\Attribute\DataSubjectId;
use Patchlevel\Hydrator\Attribute\PersonalData;
use Psr\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @see UserProfileTest
 */
#[Aggregate('user_profile')]
final class UserProfile extends BasicAggregateRoot implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    #[Id]
    #[DataSubjectId]
    private UserProfileId $id;

    #[PersonalData]
    private ?string $email = null;
    private ?string $hashedPassword = null;

    /**
     * @var list<string>
     */
    private array $roles = ['ROLE_PENDING_EMAIL_VERIFICATION'];

    public static function startWithRegistration(
        UserProfileId $id,
        string $email,
        string $password,
        UserPasswordHasherInterface $passwordHasher,
    ): self
    {
        $self = new self();
        $self->recordThat(new RegistrationStarted(
            $id,
            $email,
            $passwordHasher->hashPassword($self, $password),
        ));

        return $self;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    #[Apply]
    public function applyRegistrationStarted(RegistrationStarted $event): void
    {
        $this->id = $event->id;
        $this->email = $event->email;
        $this->hashedPassword = $event->hashedPassword;
    }

    public function verifyEmail(Accounts $accounts, ClockInterface $clock): void
    {
        // TODO: verify that email is not taken in the meantime
        $this->recordThat(new EmailVerified(
            $this->id,
            $this->email,
            $clock->now(),
            $this->hashedPassword,
        ));
    }

    #[Apply]
    public function applyEmailVerified(EmailVerified $event): void
    {
        $this->id = $event->id;
        $this->email = $event->email;
        $this->hashedPassword = $event->hashedPassword;
        $this->roles = ['ROLE_USER'];
    }

    public function id(): UserProfileId
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    /**
     * Used by the Login-Link verification
     */
    public function getId(): string
    {
        return $this->id->toString();
    }

    /**
     * Used by the Login-Link verification
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->hashedPassword;
    }

    #[\Override] public function getPassword(): ?string
    {
        return $this->hashedPassword;
    }

    #[\Override] public function getRoles(): array
    {
        return $this->roles;
    }

    #[\Override] public function eraseCredentials(): void
    {
        // TODO: check if needed
    }

    #[\Override] public function getUserIdentifier(): string
    {
        return $this->email();
    }
}