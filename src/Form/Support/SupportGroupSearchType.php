<?php

namespace App\Form\Support;

use App\Entity\SupportGroup;
use App\Form\Model\SupportGroupSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\SearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportGroupSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Nom et/ou prénom',
                    'class' => 'w-max-170',
                ],
            ])
            // ->add('familyTypologies', ChoiceType::class, [
            //     'label_attr' => ['class' => 'sr-only'],
            //     'multiple' => true,
            //     'choices' => Choices::getChoices(GroupPeople::FAMILY_TYPOLOGY),
            //     'attr' => [
            //         'class' => 'multi-select',
            //         'data-select2-id' => 'typology',
            //     ],
            //     'placeholder' => 'placeholder.familtyTypology',
            //     'required' => false,
            // ])
            // ->add("nbPeople", null, [
            //     "attr" => [
            //         "class" => "w-max-100",
            //         "placeholder" => "NbPeople",
            //         "autocomplete" => "off"
            //     ]
            // ])
            ->add('status', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'attr' => [
                    'class' => 'multi-select',
                    'data-select2-id' => 'status',
                ],
                'placeholder' => 'placeholder.status',
                'required' => false,
            ])
            ->add('supportDates', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(SupportGroupSearch::SUPPORT_DATES),
                'placeholder' => '-- Date de suivi --',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => SupportGroupSearch::class,
            ])
            ->add('service', SearchType::class, [
                'data_class' => SupportGroupSearch::class,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportGroupSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
