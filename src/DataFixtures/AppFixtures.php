<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\UserProfile\Domain\UserProfile;
use App\UserProfile\Domain\UserProfileId;
use App\UserProfile\Domain\UserProfileRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserProfileRepository       $profiles,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ClockInterface              $clock,
    ){}

    public function load(ObjectManager $manager): void
    {
        $admin = UserProfile::register(
            UserProfileId::generate(),
            'admin@example.com',
            'admin',
            $this->passwordHasher,
            $this->clock,
        );
        $admin->verifyEmail($this->clock);

        // TODO: figure out how to skip the processors
        //       I don't want to sent the email on `startWithRegistration`
        $this->profiles->save($admin);

        $pending = UserProfile::register(
            UserProfileId::generate(),
            'user1@example.com',
            'user1',
            $this->passwordHasher,
            $this->clock,
        );

        $this->profiles->save($pending);
    }
}
