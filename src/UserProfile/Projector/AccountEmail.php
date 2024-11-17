<?php declare(strict_types=1);

namespace App\UserProfile\Projector;

use App\UserProfile\Domain\Event\SignedUp;
use App\UserProfile\Domain\UserProfileId;
use Doctrine\DBAL\Connection;
use Patchlevel\EventSourcing\Attribute\Projector;
use Patchlevel\EventSourcing\Attribute\Setup;
use Patchlevel\EventSourcing\Attribute\Subscribe;
use Patchlevel\EventSourcing\Attribute\Teardown;
use Patchlevel\EventSourcing\Subscription\Subscriber\SubscriberUtil;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

#[Projector('account_email')]
final class AccountEmail
{
    use SubscriberUtil;

    public function __construct(
        private readonly Connection $connection
    ) {}

    #[Subscribe(SignedUp::class)]
    public function handleRegistration(SignedUp $event): void
    {
        $this->connection->executeStatement("
            INSERT INTO {$this->table()}
                (user_profile_id, email) 
                VALUES (:user_profile_id, :email)
        ", [
            'user_profile_id' => $event->id->toString(),
            'email' => $event->email
        ]);
    }


    #[Setup]
    public function create(): void
    {
        $this->connection->executeStatement("
            CREATE TABLE IF NOT EXISTS {$this->table()} (
                user_profile_id VARCHAR PRIMARY KEY,
                email VARCHAR UNIQUE NOT NULL
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

        if (!$profileId) {
            throw new UserNotFoundException($email);
        }

        return UserProfileId::fromString($profileId);
    }
}