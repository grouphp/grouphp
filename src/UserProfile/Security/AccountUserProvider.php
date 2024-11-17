<?php declare(strict_types=1);

namespace App\UserProfile\Security;

use App\UserProfile\Domain\UserProfile;
use App\UserProfile\Domain\UserProfileId;
use App\UserProfile\Domain\UserProfileRepository;
use App\UserProfile\Projector\AccountEmail;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<UserProfile>
 * TODO: move to AccountEmail
 */
final readonly class AccountUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserProfileRepository $profiles,
        private AccountEmail $accounts
    ) {}

    #[\Override] public function supportsClass(string $class): bool
    {
        return $class === UserProfile::class;
    }

    #[\Override] public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByIdOrUsername($identifier);
    }

    #[\Override] public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    private function loadUserByIdOrUsername(string $identifier): UserInterface
    {
        try {
            return $this->profiles->load(UserProfileId::fromString($identifier));
        } catch (InvalidUuidStringException) {
            return $this->profiles->load($this->accounts->findByEmail($identifier));
        } catch (\Exception $e) {
            throw new UserNotFoundException($identifier, previous: $e);
        }
    }
}