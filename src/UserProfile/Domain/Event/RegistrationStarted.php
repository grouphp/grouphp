<?php declare(strict_types=1);

namespace App\UserProfile\Domain\Event;

use App\UserProfile\Domain\UserProfileId;
use Patchlevel\EventSourcing\Attribute\Event;
use Patchlevel\Hydrator\Attribute\PersonalData;

#[Event(name: 'registration.started')]
final class RegistrationStarted
{
    public function __construct(
        public UserProfileId $id,
        #[PersonalData(fallback: 'unknown')]
        public string $email,
        public string $hashedPassword,
    ) {}
}