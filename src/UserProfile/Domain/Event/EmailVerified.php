<?php declare(strict_types=1);

namespace App\UserProfile\Domain\Event;

use App\UserProfile\Domain\UserProfileId;
use Patchlevel\EventSourcing\Attribute\Event;
use Patchlevel\Hydrator\Attribute\DataSubjectId;
use Patchlevel\Hydrator\Attribute\PersonalData;

#[Event(name: 'user_profile.email_verified')]
final readonly class EmailVerified
{
    public function __construct(
        #[DataSubjectId]
        public UserProfileId $id,
        #[PersonalData(fallback: 'redacted')]
        public string $email,
        public \DateTimeImmutable $emailVerifiedAt,
        public string $hashedPassword
    ) {}
}