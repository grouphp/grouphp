<?php declare(strict_types=1);

namespace App\UserProfile\Domain;

use App\UserProfile\Domain\Event\RegistrationStarted;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher;

final class UserProfileTest extends TestCase
{
    public function testHashesPassword(): void
    {
        $id = UserProfileId::generate();
        $profile = UserProfile::startWithRegistration(
            $id,
            'foo@email.com',
            'password124',
            new NativePasswordHasher()
        );

        self::assertCount(1, $profile->releaseEvents());

        self::assertSame('foo@email.com', $profile->email());
        // It's not hashed since we use the PlainTextPasswordHasher in that case...
        self::assertNotSame('password124', $profile->password());
    }

    public function testRegistration(): void
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

        self::assertSame('foo@email.com', $profile->email());
        // It's not hashed since we use the PlainTextPasswordHasher in that case...
        self::assertSame('password124', $profile->password());
    }
}