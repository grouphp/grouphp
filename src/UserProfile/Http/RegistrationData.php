<?php declare(strict_types=1);

namespace App\UserProfile\Http;

use Symfony\Component\Validator\Constraints as Assert;

final class RegistrationData
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\PasswordStrength]
    #[Assert\NotCompromisedPassword]
    public string $password;
}