<?php

namespace App\Form\Rdv;

use App\Form\Model\RdvSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceSearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RdvSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Title',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Suivi concerné',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => RdvSearch::class,
            ])
            ->add('service', ServiceSearchType::class, [
                'data_class' => RdvSearch::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RdvSearch::class,
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
