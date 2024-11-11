<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('dashboard')]
final class Dashboard extends AbstractController
{
    public function __invoke(UserInterface $user): Response
    {
        return new Response('<body>OK</body>');
    }
}