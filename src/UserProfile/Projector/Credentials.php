<?php declare(strict_types=1);

namespace App\UserProfile\Projector;

use App\UserProfile\Domain\Event\RegistrationStarted;
use App\UserProfile\Domain\UserProfileId;
use Doctrine\DBAL\Connection;
use Patchlevel\EventSourcing\Attribute\Setup;
use Patchlevel\EventSourcing\Attribute\Subscribe;
use Patchlevel\EventSourcing\Attribute\Subscriber;
use Patchlevel\EventSourcing\Attribute\Teardown;
use Patchlevel\EventSourcing\Subscription\RunMode;
use Patchlevel\EventSourcing\Subscription\Subscriber\SubscriberUtil;

#[Subscriber('credentials', RunMode::FromBeginning)]
final class Credentials
{
    use SubscriberUtil;

    public function __construct(
        private Connection $connection,
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

    public function findByEmail(string $email): ?UserProfileId
    {
        $profileId = $this->connection->executeQuery(
            "SELECT user_profile_id FROM {$this->table()} WHERE email = :email",
            [
                'email' => $email,
            ]
        )->fetchOne();

        return UserProfileId::fromString($profileId);
    }
}