<?php declare(strict_types=1);

namespace App\UserProfile\Projector;

use App\UserProfile\Domain\Event\EmailVerified;
use App\UserProfile\Domain\Event\RegistrationStarted;
use App\UserProfile\Domain\UserProfileId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Patchlevel\EventSourcing\Attribute\Setup;
use Patchlevel\EventSourcing\Attribute\Subscribe;
use Patchlevel\EventSourcing\Attribute\Subscriber;
use Patchlevel\EventSourcing\Attribute\Teardown;
use Patchlevel\EventSourcing\Subscription\RunMode;
use Patchlevel\EventSourcing\Subscription\Subscriber\SubscriberUtil;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

#[Subscriber('accounts', RunMode::FromBeginning)]
final class Accounts
{
    use SubscriberUtil;

    public function __construct(
        private readonly Connection $connection
    ) {}

    #[Subscribe(RegistrationStarted::class)]
    public function handleRegistrationStarted(RegistrationStarted $event): void
    {
        $this->connection->executeStatement("
            INSERT INTO {$this->table()}
                (user_profile_id, email_verified_at, email, password) 
               VALUES (:user_profile_id, NULL, :email, :password)
        ", [
            'user_profile_id' => $event->id->toString(),
            'email' => $event->email,
            'password' => $event->hashedPassword,
        ]);
    }

    #[Subscribe(EmailVerified::class)]
    public function handleEmailVerified(EmailVerified $event): void
    {
        $this->connection->executeStatement("
            UPDATE {$this->table()}
                SET email_verified_at = :email_verified_at
                WHERE user_profile_id = :user_profile_id
                AND email = :email
        ", [
            'user_profile_id' => $event->id->toString(),
            'email_verified_at' => $event->emailVerifiedAt->format(\DateTimeInterface::RFC3339_EXTENDED),
            'email' => $event->email,
        ]);
    }


    #[Setup]
    public function create(): void
    {
        $this->connection->executeStatement("
            CREATE TABLE IF NOT EXISTS {$this->table()} (
                user_profile_id VARCHAR PRIMARY KEY,
                email_verified_at TIMESTAMP DEFAULT NULL,
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
        try {
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

        } catch (TableNotFoundException) {
            throw new UserNotFoundException($email);
        }
    }
}