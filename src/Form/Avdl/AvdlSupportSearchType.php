<?php

namespace App\Form\Avdl;

use App\Entity\Avdl;
use App\Entity\Service;
use App\Form\Utils\Choices;
use App\Entity\SupportGroup;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceSearchType;
use App\Form\Model\AvdlSupportSearch;
use App\Form\Model\SupportGroupSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AvdlSupportSearchType extends AbstractType
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
            ->add('status', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'attr' => [
                    'class' => 'multi-select w-min-120',
                    'data-select2-id' => 'status',
                ],
                'placeholder' => '-- Status --',
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
                ->add('service', ServiceSearchType::class, [
                    'data_class' => AvdlSupportSearch::class,
                    'attr' => [
                        'options' => ['devices', 'referents'],
                        'serviceId' => Service::SERVICE_AVDL_ID,
                    ],
            ])
            ->add('diagOrSupport', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(AvdlSupportSearch::DIAG_OR_SUPPORT),
                'placeholder' => '-- Diag/Acc. --',
                'required' => false,
            ])
            ->add('supportType', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(Avdl::SUPPORT_TYPE),
                'attr' => [
                    'class' => 'multi-select w-min-120',
                    'data-select2-id' => 'support-type',
                ],
                'placeholder' => '-- Type --',
                'required' => false,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AvdlSupportSearch::class,
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
