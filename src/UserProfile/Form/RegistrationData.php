<?php declare(strict_types=1);

namespace App\UserProfile\Form;

use Symfony\Component\Validator\Constraints as Assert;
final class RegistrationData
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public $email;

    #[Assert\NotBlank]
    #[Assert\PasswordStrength]
    #[Assert\NotCompromisedPassword]
    public $password;
}