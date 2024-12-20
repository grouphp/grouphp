<?php declare(strict_types=1);

namespace App\UserProfile\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('email_verify', name: 'email_verify')]
#[AsController]
final class EmailVerificationLink extends AbstractController
{
    public function __invoke(): never
    {
        throw new \LogicException('This code should never be reached as it is handled by the security config');
    }
}