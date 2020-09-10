<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['attr'];

        $builder
            ->add('search', null, [
                'label' => $data['seachLabel'] ?? '',
                'attr' => [
                    'class' => 'js-search',
                    'placeholder' => 'location.search.placeholder',
                    'autocomplete' => 'off',
                ],
                'help' => $data['searchHelp'] ?? null,
                'mapped' => false,
            ])
            ->add('commentLocation', null, [
                'help' => $data['commentLocationHelp'] ?? 'commentLocation.help',
            ])
            ->add('address', null, [
                'label' => 'location.address_auto',
                'attr' => [
                    'class' => 'js-address',
                    'readonly' => true,
                ],
            ])
            ->add('city', null, [
                'label' => 'location.city_auto',
                'attr' => [
                    'class' => 'js-city',
                    'readonly' => true,
                ],
            ])
            ->add('zipcode', null, [
                'label' => 'location.zipcode_auto',
                'attr' => [
                    'class' => 'js-zipcode',
                    'readonly' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
