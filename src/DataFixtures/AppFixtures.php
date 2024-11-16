<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\UserProfile\Domain\UserProfile;
use App\UserProfile\Domain\UserProfileId;
use App\UserProfile\Domain\UserProfileRepository;
use App\UserProfile\Projector\Accounts;
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
        private readonly Accounts                    $accounts,
    ){}

    public function load(ObjectManager $manager): void
    {
        $admin = UserProfile::register(
            UserProfileId::generate(),
            'admin@example.com',
            'admin',
            $this->passwordHasher
        );
        $admin->verifyEmail($this->accounts, $this->clock);

        // TODO: figure out how to skip the processors
        //       I don't want to sent the email on `startWithRegistration`
        $this->profiles->save($admin);
    }
}
