<?php declare(strict_types=1);

namespace App\UserProfile\Domain;

use App\UserProfile\Domain\Event\RegistrationStarted;
use Patchlevel\EventSourcing\Aggregate\BasicAggregateRoot;
use Patchlevel\EventSourcing\Attribute\Aggregate;
use Patchlevel\EventSourcing\Attribute\Apply;
use Patchlevel\EventSourcing\Attribute\Id;
use Patchlevel\Hydrator\Attribute\DataSubjectId;
use Patchlevel\Hydrator\Attribute\PersonalData;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @see UserProfileTest
 */
#[Aggregate('user_profile')]
final class UserProfile extends BasicAggregateRoot implements PasswordAuthenticatedUserInterface
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

    public function id(): UserProfileId
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->hashedPassword;
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

    #[\Override] public function getPassword(): ?string
    {
        return $this->hashedPassword;
    }
}