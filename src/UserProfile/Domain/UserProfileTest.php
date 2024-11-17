<?php declare(strict_types=1);

namespace App\UserProfile\Domain;

use App\UserProfile\Domain\Event\EmailVerified;
use App\UserProfile\Domain\Event\SignedUp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class UserProfileTest extends TestCase
{
    private UserPasswordHasherInterface $passwordHasher;

    public function testSignup(): void
    {
        $id = UserProfileId::generate();
        $clock = new MockClock();
        $profile = UserProfile::register(
            $id,
            'foo@email.com',
            'password124',
            $this->passwordHasher,
            $clock
        );

        self::assertEquals(
            [
                new SignedUp($id, 'foo@email.com', 'hashed:password124', $clock->now()),
            ],
            $profile->releaseEvents()
        );

        self::assertSame('foo@email.com', $profile->email());
        self::assertNull(
            $profile->password(),
            'The password should not be set, as the user should not be able to login, before verifying the email'
        );
        // It's not hashed since we use the PlainTextPasswordHasher in that case...
    }

    public function testValidatingEmailShouldAllowToLogin(): void
    {
        $id = UserProfileId::generate();
        $clock = new MockClock();

        $profile = UserProfile::createFromEvents([
            new SignedUp($id, 'foo@email.com', 'hashed:password124', $clock->now()),
        ]);
        $profile->verifyEmail($clock);

        self::assertEquals(
            [
                new EmailVerified($id, 'foo@email.com', $clock->now()),
            ],
            $profile->releaseEvents()
        );

        self::assertSame('foo@email.com', $profile->email());
        self::assertSame('hashed:password124', $profile->password());
    }

    public function setUp(): void
    {
        $this->passwordHasher = new class implements UserPasswordHasherInterface {

            #[\Override] public function hashPassword(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): string
            {
                return 'hashed:'.$plainPassword;
            }

            #[\Override] public function isPasswordValid(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): bool
            {
                return true;
            }

            #[\Override] public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
            {
                return false;
            }
        };
    }
}