<?php

namespace App\Form;

use App\Entity\UserResetPass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("username", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Login"
                ]
            ])
            ->add("email", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Email"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserResetPass::class,
            "translation_domain" => "forms",
        ]);
    }
}
