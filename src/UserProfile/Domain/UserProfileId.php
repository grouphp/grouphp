<?php declare(strict_types=1);

namespace App\UserProfile\Domain;

use Patchlevel\EventSourcing\Aggregate\AggregateRootId;
use Patchlevel\EventSourcing\Aggregate\RamseyUuidV7Behaviour;

final readonly class UserProfileId implements AggregateRootId
{
    use RamseyUuidV7Behaviour;
}