<?php

namespace App\Form\Support\Support;

use App\Entity\Organization\Accommodation;
use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Entity\Support\AccommodationGroup;
use App\Entity\Support\SupportGroup;
use App\Form\Organization\Accommodation\AccommodationGroupHotelType;
use App\Form\Support\Avdl\AvdlType;
use App\Form\Support\HotelSupport\HotelSupportType;
use App\Form\Support\OriginRequest\OriginRequestType;
use App\Form\Type\LocationType;
use App\Form\Utils\Choices;
use App\Repository\Organization\AccommodationRepository;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\Organization\UserRepository;
use App\Security\CurrentUserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportGroupType extends AbstractType
{
    private $currentUser;

    public function __construct(CurrentUserService $currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'query_builder' => function (ServiceRepository $repo) {
                    return $repo->getServicesOfUserQueryList($this->currentUser);
                },
                'placeholder' => 'placeholder.select',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::STATUS),
                'placeholder' => 'placeholder.select',
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('theoreticalEndDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(SupportGroup::END_STATUS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endStatusComment')
            ->add('endAccommodation', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
                'help' => 'endAccommodation.help',
            ])
            ->add('agreement', CheckboxType::class, [
                'required' => true,
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
            ])
            ->add('supportPeople', CollectionType::class, [
                'entry_type' => SupportPersonType::class,
                'label' => null,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ])
            ->add('location', LocationType::class, [
                'data_class' => SupportGroup::class,
                'attr' => [
                    'geoLocation' => true,
                    'searchHelp' => 'location.search.help',
                ],
            ])
            ->add('comment', null, [
                'attr' => ['placeholder' => 'comment.placeholder'],
            ])
            ->add('cloneSupport', HiddenType::class, [
                'mapped' => false,
            ]);

        $formModifier = $this->formModifier();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $form = $event->getForm();
            $supportGroup = $event->getData();
            $service = $supportGroup->getService();
            $subService = $supportGroup->getSubService();

            $formModifier($form, $service, $subService);
            $this->addSupportFields($form, $service);
        });

        $builder->get('service')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $form = $event->getForm();
            $service = $form->getData();

            $formModifier($form->getParent(), $service);
        });
    }

    protected function formModifier()
    {
        return function (FormInterface $form, ?Service $service = null, ?SubService $subService = null) {
            $serviceId = $service ? $service->getId() : null;
            $subServiceId = $subService ? $subService->getId() : null;
            $optionsReferent = $this->optionsReferent($serviceId);

            $form
                ->add('subService', EntityType::class, [
                    'class' => SubService::class,
                    'choice_label' => 'name',
                    'query_builder' => function (SubServiceRepository $repo) use ($serviceId) {
                        return $repo->getSubServicesOfUserQueryList($this->currentUser, $serviceId);
                    },
                    'placeholder' => 'placeholder.select',
                    'required' => false,
                ])
                ->add('device', EntityType::class, [
                    'class' => Device::class,
                    'choice_label' => 'name',
                    'query_builder' => function (DeviceRepository $repo) use ($serviceId) {
                        return $repo->getDevicesOfUserQueryList($this->currentUser, $serviceId);
                    },
                    'placeholder' => 'placeholder.select',
                ])
                ->add('referent', EntityType::class, $optionsReferent)
                ->add('referent2', EntityType::class, $optionsReferent)
                ->add('accommodation', EntityType::class, [
                    'class' => Accommodation::class,
                    'choice_label' => 'name',
                    'query_builder' => function (AccommodationRepository $repo) use ($serviceId, $subServiceId) {
                        return $repo->getAccommodationsQueryList($serviceId, $subServiceId);
                    },
                    'label' => Service::SERVICE_PASH_ID === $serviceId ? 'hotelName' : 'accommodation.name',
                    'placeholder' => 'placeholder.select',
                    'mapped' => false,
                    'required' => false,
                ])
                // ->add('accommodationGroups', CollectionType::class, [
                //     'entry_type' => AccommodationGroupHotelType::class,
                //     'label' => null,
                //     'allow_add' => false,
                //     'allow_delete' => false,
                //     'delete_empty' => true,
                //     'attr' => ['serviceId' => $serviceId],
                //     // 'by_reference' => true,
                // ])
                ->add('originRequest', OriginRequestType::class, [
                    'attr' => ['serviceId' => $serviceId],
                ]);
        };
    }

    protected function addSupportFields(FormInterface $form, Service $service)
    {
        switch ($service->getId()) {
            case Service::SERVICE_AVDL_ID:
                $this->addAvdlFields($form);
                break;
            case Service::SERVICE_PASH_ID:
                $this->addHotelFields($form);
                break;
         }
    }

    protected function addAvdlFields(FormInterface $form)
    {
        $form
            ->remove('startDate')
            ->remove('endDate')
            ->remove('endStatusComment')
            ->add('avdl', AvdlType::class);
    }

    protected function addHotelFields(FormInterface $form)
    {
        $form
            ->remove('location')
            ->add('hotelSupport', HotelSupportType::class);

        $supportGroup = $form->getConfig()->getData();

        if (0 === $supportGroup->getAccommodationGroups()->count()) {
            $this->addAccommodationGroup($supportGroup);
        }

        $form->add('accommodationGroups', CollectionType::class, [
                'entry_type' => AccommodationGroupHotelType::class,
                'label' => null,
                'allow_add' => false,
                'allow_delete' => false,
                'delete_empty' => true,
                'attr' => [
                    'serviceId' => $supportGroup->getService() ? $supportGroup->getService()->getId() : null,
                    'subServiceId' => $supportGroup->getSubService() ? $supportGroup->getSubService()->getId() : null,
                ],
            ]);
    }

    protected function addAccommodationGroup(SupportGroup $supportGroup)
    {
        $accommodationGroup = (new AccommodationGroup())->setPeopleGroup($supportGroup->getPeopleGroup());
        $supportGroup->addAccommodationGroup($accommodationGroup);

        return $supportGroup;
    }

    /**
     * Retourne les options du champ Référent.
     */
    protected function optionsReferent(?int $serviceId): array
    {
        return [
            'class' => User::class,
            'choice_label' => 'fullname',
            'query_builder' => function (UserRepository $repo) use ($serviceId) {
                return $repo->getUsersQueryList($serviceId, $this->currentUser->getUser());
            },
            'placeholder' => 'placeholder.select',
            'required' => false,
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupportGroup::class,
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'support';
    }
}