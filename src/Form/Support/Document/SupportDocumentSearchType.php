<?php

namespace App\Form\Support\Document;

use App\Entity\Support\Document;
use App\Form\Model\Support\SupportDocumentSearch;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportDocumentSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Search',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getchoices(Document::TYPE),
                'attr' => [
                    'class' => 'w-max-150',
                ],
                'placeholder' => 'placeholder.type',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportDocumentSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'search';
    }
}
