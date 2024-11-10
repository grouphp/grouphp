<?php declare(strict_types=1);

namespace App\UserProfile\Http;

use Symfony\Component\Validator\Constraints as Assert;

final class LoginData
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public $email;

    #[Assert\NotBlank]
    public $password;
}