<?php declare(strict_types=1);

namespace App\UserProfile\Security;

use App\UserProfile\Domain\UserProfile;
use App\UserProfile\Domain\UserProfileRepository;
use App\UserProfile\Projector\ActiveAccounts;
use Patchlevel\EventSourcing\Repository\AggregateNotFound;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class ActiveAccountUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserProfileRepository $profiles,
        private ActiveAccounts $accounts
    ) {}
    #[\Override] public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserProfile) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->profiles->load($user->id());
    }

    #[\Override] public function supportsClass(string $class): bool
    {
        return $class === UserProfile::class;
    }

    #[\Override] public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            return $this->profiles->load($this->accounts->findByEmail($identifier));
        } catch (AggregateNotFound $e) {
            throw new UserNotFoundException($identifier, previous: $e);
        }
    }
}