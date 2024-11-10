<?php declare(strict_types=1);

namespace App\UserProfile\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RegistrationType extends AbstractType
{
    #[\Override] public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'email',
            EmailType::class
        );

        $builder->add('password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ]
        );
    }

    #[\Override] public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationData::class
        ]);
    }
}