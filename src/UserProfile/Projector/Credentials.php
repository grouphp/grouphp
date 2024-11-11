<?php declare(strict_types=1);

namespace App\UserProfile\Projector;

use App\UserProfile\Domain\Event\RegistrationStarted;
use App\UserProfile\Domain\UserProfile;
use App\UserProfile\Domain\UserProfileId;
use App\UserProfile\Domain\UserProfileRepository;
use Doctrine\DBAL\Connection;
use Patchlevel\EventSourcing\Attribute\Setup;
use Patchlevel\EventSourcing\Attribute\Subscribe;
use Patchlevel\EventSourcing\Attribute\Subscriber;
use Patchlevel\EventSourcing\Attribute\Teardown;
use Patchlevel\EventSourcing\Repository\AggregateNotFound;
use Patchlevel\EventSourcing\Subscription\RunMode;
use Patchlevel\EventSourcing\Subscription\Subscriber\SubscriberUtil;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

#[Subscriber('credentials', RunMode::FromBeginning)]
final class Credentials implements UserProviderInterface
{
    use SubscriberUtil;

    public function __construct(
        private Connection $connection,
        private UserProfileRepository $profiles,
    ) {}

    #[Subscribe(RegistrationStarted::class)]
    public function handleRegistrationStarted(RegistrationStarted $event): void
    {
        $this->connection->executeStatement("
            INSERT INTO {$this->table()}
                (user_profile_id, email, password) 
               VALUES (:user_profile_id, :email, :password)
        ", [
            'user_profile_id' => $event->id->toString(),
            'email' => $event->email,
            'password' => $event->hashedPassword,
        ]);
    }


    #[Setup]
    public function create(): void
    {
        $this->connection->executeStatement("
            CREATE TABLE IF NOT EXISTS {$this->table()} (
                user_profile_id VARCHAR PRIMARY KEY,
                email VARCHAR UNIQUE NOT NULL,
                password VARCHAR NOT NULL
             );
        ");
    }

    #[Teardown]
    public function drop(): void
    {
        $this->connection->executeStatement("DROP TABLE IF EXISTS {$this->table()};");
    }

    private function table(): string
    {
        return 'projection_' . $this->subscriberId();
    }

    public function findByEmail(string $email): UserProfileId
    {
        $profileId = $this->connection->executeQuery(
            "SELECT user_profile_id FROM {$this->table()} WHERE email = :email",
            [
                'email' => $email,
            ]
        )->fetchOne();

        if (! $profileId) {
            throw new UserNotFoundException($email);
        }

        return UserProfileId::fromString($profileId);
    }

    #[\Override] public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserProfile) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->profiles->load($user->id());
    }

    #[\Override] public function supportsClass(string $class): bool
    {
        return $class === UserProfile::class;
    }

    #[\Override] public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            return $this->profiles->load($this->findByEmail($identifier));
        } catch (AggregateNotFound $e) {
            throw new UserNotFoundException($identifier, previous: $e);
        }
    }
}