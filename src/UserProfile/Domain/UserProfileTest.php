<?php declare(strict_types=1);

namespace App\UserProfile\Domain;

use App\UserProfile\Domain\Event\RegistrationStarted;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher;

final class UserProfileTest extends TestCase
{
    public function testAccountCreation(): void
    {
        $id = UserProfileId::generate();
        $profile = UserProfile::startWithRegistration(
            $id,
            'foo@email.com',
            'password124',
            new PlaintextPasswordHasher()
        );

        self::assertEquals(
            [
                new RegistrationStarted($id, 'foo@email.com', 'password124'),
            ],
            $profile->releaseEvents()
        );
    }
}