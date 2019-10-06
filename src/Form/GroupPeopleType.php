<?php

namespace App\Form;

use App\Entity\GroupPeople;
use App\Entity\RolePerson;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class GroupPeopleType extends AbstractType
{
    public const FAMILY_TYPOLOGY = [
        "-- Sélectionner --" => NULL,
        "Femme seule" => 1,
        "Homme seul" => 2,
        "Couple sans enfant" => 3,
        "Femme seule avec enfant(s)" => 4,
        "Homme seul avec enfant(s)" => 5,
        "Couple avec enfant(s)" => 6,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add("familyTypology", ChoiceType::class, [
            "label" => "Typologie familiale",
            "attr" => [
                "class" => "col-md-12"
            ],
            "choices" => $this->getFamilyTypologyType()
            ])
        ->add("nbPeople", NULL, [
            "label" => "Nombre de personnes",
            "attr" => [
                "class" => "col-md-4"
            ]
        ])
        ->add('rolePerson', CollectionType::class, [
            'entry_type'   => RolePersonType::class,
            'allow_add'    => true,
            'allow_delete' => false
        ])
        // ->add('rolePerson', EntityType::class, [
        //     'class'        => RolePerson::class,
        //     'choice_label' => 'role',
        //     'multiple'     => true,
        // ])
        ->add("comment", NULL, [
            "label" => "Commentaire",
            "attr" => [
                "rows" => 5,
                "placeholder" => "Saisir un commentaire sur le ménage"
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => GroupPeople::class,
        ]);
    }

    public function getFamilyTypologyType() 
    {
        return self::FAMILY_TYPOLOGY;
    }
}
