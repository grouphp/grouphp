<?php declare(strict_types=1);

namespace App\UserProfile\Domain;

use App\UserProfile\Domain\Event\RegistrationStarted;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class UserProfileTest extends TestCase
{
    private UserPasswordHasherInterface $passwordHasher;

    public function testRegistration(): void
    {
        $id = UserProfileId::generate();
        $profile = UserProfile::register(
            $id,
            'foo@email.com',
            'password124',
            $this->passwordHasher,
        );

        self::assertEquals(
            [
                new RegistrationStarted($id, 'foo@email.com', 'hashed:password124'),
            ],
            $profile->releaseEvents()
        );

        self::assertSame('foo@email.com', $profile->email());
        // It's not hashed since we use the PlainTextPasswordHasher in that case...
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