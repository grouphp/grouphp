<?php declare(strict_types=1);

namespace App\UserProfile\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pending-email-verification')]
final class PendingEmailVerification extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('pending_email_verification.html.twig');
    }
}