<?php declare(strict_types=1);

namespace App\Backoffice;

use App\UserProfile\Http\Login;
use App\UserProfile\Http\Registration;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class MenuBuilder
{
    public function __construct(
        private FactoryInterface $factory,
        private Security $security,
    ){}
    public function createUserMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('account');
        $user = $this->security->getUser();

        if ($user) {
            // TODO: pending implementation
            $menu->addChild('Logout', ['route' => '_logout_main']);
            $menu->addChild('Profile', ['route' => '_logout_main']);
        } else {
            // TODO: pending implementation
            $menu->addChild('Login', ['route' => Login::class]);
            $menu->addChild('Registration', ['route' => Registration::class]);
        }

        return $menu;
    }

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $user = $this->security->getUser();

        $admin = $menu->addChild('Admin', ['route' => '_logout_main']);
        $admin->addChild('Users', ['route' => '_logout_main']);
        $admin->addChild('Settings', ['route' => '_logout_main']);

        return $menu;
    }
}