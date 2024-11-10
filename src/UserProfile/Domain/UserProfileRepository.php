<?php declare(strict_types=1);

namespace App\UserProfile\Domain;

use Patchlevel\EventSourcing\Repository\Repository;
use Patchlevel\EventSourcing\Repository\RepositoryManager;

final class UserProfileRepository
{
    /**
     * @var Repository<UserProfile>
     **/
    private Repository $repository;

    public function __construct(RepositoryManager $repositoryManager)
    {
        $this->repository = $repositoryManager->get(UserProfile::class);
    }

    public function load(UserProfileId $id): UserProfile
    {
        return $this->repository->load($id);
    }

    public function save(UserProfile $profile): void
    {
        $this->repository->save($profile);
    }

    public function has(UserProfileId $id): bool
    {
        return $this->repository->has($id);
    }
}