<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

// TODO: where should I place that one?
#[Route('dashboard')]
final class Dashboard extends AbstractController
{
    public function __invoke(UserInterface $user): Response
    {
        return $this->render('dashboard.html.twig');
    }
}