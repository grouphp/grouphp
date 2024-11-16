<?php declare(strict_types=1);

namespace App\UserProfile\Projector;

use App\UserProfile\Domain\Event\EmailVerified;
use App\UserProfile\Domain\Event\RegistrationStarted;
use App\UserProfile\Domain\UserProfileId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Patchlevel\EventSourcing\Attribute\Projector;
use Patchlevel\EventSourcing\Attribute\Setup;
use Patchlevel\EventSourcing\Attribute\Subscribe;
use Patchlevel\EventSourcing\Attribute\Subscriber;
use Patchlevel\EventSourcing\Attribute\Teardown;
use Patchlevel\EventSourcing\Subscription\RunMode;
use Patchlevel\EventSourcing\Subscription\Subscriber\SubscriberUtil;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

#[Projector('active_accounts')]
final class ActiveAccounts
{
    use SubscriberUtil;

    public function __construct(
        private readonly Connection $connection
    ) {}

    #[Subscribe(EmailVerified::class)]
    public function handleEmailVerified(EmailVerified $event): void
    {
        $this->connection->executeStatement("
            INSERT INTO {$this->table()}
                (user_profile_id, email, activated_at) 
                VALUES (:user_profile_id, :email, :activated_at)
        ", [
            'user_profile_id' => $event->id->toString(),
            'email' => $event->email,
            'activated_at' => $event->emailVerifiedAt->format(\DateTimeInterface::RFC3339),
        ]);
    }


    #[Setup]
    public function create(): void
    {
        $this->connection->executeStatement("
            CREATE TABLE IF NOT EXISTS {$this->table()} (
                user_profile_id VARCHAR PRIMARY KEY,
                email VARCHAR UNIQUE NOT NULL,
                activated_at TIMESTAMP DEFAULT NULL
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

        } catch (TableNotFoundException $e) {
            throw new UserNotFoundException($email, previous: $e);
        }
    }
}