<?php

namespace App\Form\Admin\Security;

use App\Form\Utils\Choices;
use App\Entity\Organization\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use App\Form\Organization\User\UserDeviceType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Organization\Service\ServiceUserType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SecurityUserType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', null, [
                'attr' => [
                    'class' => 'text-capitalize',
                    'placeholder' => 'Firstname',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('lastname', null, [
                'attr' => [
                    'class' => 'text-capitalize',
                    'placeholder' => 'Lastname',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('username', null, [
                'attr' => [
                    'placeholder' => 'Login',
                    'autocomplete' => 'off',
                ],
                'help' => 'user.username.help',
            ])
            ->add('email', null, [
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('phone1', null, [
                'attr' => [
                    'class' => 'js-phone',
                    'placeholder' => 'Phone',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'user.status',
                'choices' => Choices::getChoices(User::STATUS),
                'placeholder' => 'placeholder.select',
                'required' => true,
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'user.roles',
                'choices' => $this->getRoles(),
                'multiple' => true,
                'attr' => ['class' => 'h-max-76'],
                'placeholder' => 'placeholder.select',
            ])
            // ->add('password', PasswordType::class, [
            //     'attr' => [
            //         'class' => 'js-password',
            //         'placeholder' => 'Password',
            //     ],
            //     'help' => 'user.password.help',
            // ])
            // ->add('confirmPassword', PasswordType::class, [
            //     'attr' => [
            //         'class' => 'js-password',
            //         'placeholder' => 'Confirm password',
            //     ],
            //     'help' => 'user.confirmPassword.help',
            // ])
            ->add('serviceUser', CollectionType::class, [
                'entry_type' => ServiceUserType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
                'label_attr' => ['class' => 'sr-only'],
                'entry_options' => [
                    'attr' => ['class' => 'form-inline'],
                ],
            ])
            ->add('userDevices', CollectionType::class, [
                'entry_type' => UserDeviceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
                'label_attr' => ['class' => 'sr-only'],
                'entry_options' => [
                    'attr' => ['class' => 'form-inline'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getRoles()
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return Choices::getChoices(User::ROLES);
        }

        return [
            'Utilisateur' => 'ROLE_USER',
            'Administrateur' => 'ROLE_ADMIN',
        ];
    }

    public function getBlockPrefix()
    {
        return 'user';
    }
}
