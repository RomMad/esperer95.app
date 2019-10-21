<?php

namespace App\Form;

use App\Entity\Person;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PersonType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("lastname", null, [
                "attr" => [
                    "class" => "text-uppercase",
                    "placeholder" => "Lastname"
                ]
            ])
            ->add("firstname", null, [
                "attr" => [
                    "placeholder" => "Firstname"
                ]
            ])
            ->add("birthdate", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "class" => "col-md-12"
                ],
                "required" => true

            ])
            ->add("gender", ChoiceType::class, [
                "attr" => [
                    "class" => "col-md-12"
                ],
                "choices" => $this->getchoices(Person::GENDER),
                "placeholder" => "-- Select --",
                "required" => true
            ])
            ->add("phone1")
            ->add("phone2")
            ->add("email")
            ->add("comment", null, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the person"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Person::class,
            "translation_domain" => "forms"
        ]);
    }
}
