<?php

namespace App\Service;

use App\Entity\Device;
use App\Entity\Service;
use App\Repository\DeviceRepository;
use App\Security\CurrentUserService;
use App\Repository\ServiceRepository;
use App\Repository\AccommodationRepository;
use App\Repository\AccommodationPersonRepository;

class OccupancyRate
{
    protected $currentUser;
    protected $repoAccommodation;
    protected $repoAccommodatioPerson;
    protected $repoService;
    protected $repoDevice;

    protected $datas = [];
    protected $nbPlaces = 0;
    protected $nbAccommodations = 0;
    protected $capacityDays = 0;
    protected $nbAccommodationsPeople = 0;
    protected $occupancyDays = 0;

    public function __construct(
        CurrentUserService $currentUser,
        AccommodationRepository $repoAccommodation,
        AccommodationPersonRepository $repoAccommodatioPerson,
        ServiceRepository $repoService,
        DeviceRepository $repoDevice)
    {
        $this->currentUser = $currentUser;
        $this->repoAccommodation = $repoAccommodation;
        $this->repoAccommodatioPerson = $repoAccommodatioPerson;
        $this->repoService = $repoService;
        $this->repoDevice = $repoDevice;
    }

    /**
     * Donne tous les dispositifs avec leur taux d'occupation.
     */
    public function getOccupancyRateByDevice(\DateTime $start, \DateTime $end): array
    {
        $devices = $this->repoDevice->findDevicesWithAccommodation($this->currentUser, $start, $end);
        $accommodationPeople = $this->repoAccommodatioPerson->findAccommodationPeople($this->currentUser, $start, $end);
        $interval = date_diff($start, $end);

        foreach ($devices as $device) {
            $nbPlaces = 0;
            $nbAccommodations = 0;
            $capacityDays = 0;
            $nbAccommodationsPeople = 0;
            $occupancyDays = 0;

            foreach ($device->getAccommodations() as $accommodation) {
                $dateInterval = $this->getDateInterval($accommodation->getStartDate(), $accommodation->getEndDate(), $start, $end);
                $capacityDays += $dateInterval->format('%a') * $accommodation->getNbPlaces();
                $nbPlaces += $accommodation->getNbPlaces();
                ++$nbAccommodations;
            }

            foreach ($accommodationPeople as $accommodationPerson) {
                if ($accommodationPerson->getAccommodationGroup()->getAccommodation()->getDevice() == $device) {
                    $dateInterval = $this->getDateInterval($accommodationPerson->getStartDate(), $accommodationPerson->getEndDate(), $start, $end);
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbAccommodationsPeople;
                }
            }

            $this->datas[$device->getId()] = [
                'name' => $device->getName(),
                'nbAccommodations' => $nbAccommodations,
                'nbPlaces' => $nbPlaces,
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbAccommodations ? ($capacityDays / $interval->format('%a')) : null,
                'averageOccupancy' => $nbAccommodationsPeople ? ($occupancyDays / $interval->format('%a')) : null,
            ];

            $this->nbAccommodations += $nbAccommodations;
            $this->nbPlaces += $nbPlaces;
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbAccommodationsPeople += $nbAccommodationsPeople;
        }

        return [
            'devices' => $this->datas,
            'interval' => $interval->format('%a'),
            'nbAccommodations' => $this->nbAccommodations,
            'nbPlaces' => $this->nbPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $this->nbAccommodations ? ($this->capacityDays / $interval->format('%a')) : null,
            'averageOccupancy' => $this->nbAccommodationsPeople ? ($this->occupancyDays / $interval->format('%a')) : null,
        ];
    }

    /**
     * Donne tous les services avec leur taux d'occupation.
     */
    public function getOccupancyRateByService(\DateTime $start, \DateTime $end, Device $device = null): array
    {
        $services = $this->repoService->findServicesWithAccommodation($this->currentUser, $start, $end, $device);
        $accommodationPeople = $this->repoAccommodatioPerson->findAccommodationPeople($this->currentUser, $start, $end);
        $interval = date_diff($start, $end);

        foreach ($services as $service) {
            $nbPlaces = 0;
            $nbAccommodations = 0;
            $capacityDays = 0;
            $nbAccommodationsPeople = 0;
            $occupancyDays = 0;

            foreach ($service->getAccommodations() as $accommodation) {
                $dateInterval = $this->getDateInterval($accommodation->getStartDate(), $accommodation->getEndDate(), $start, $end);
                $capacityDays += $dateInterval->format('%a') * $accommodation->getNbPlaces();
                $nbPlaces += $accommodation->getNbPlaces();
                ++$nbAccommodations;
            }

            foreach ($accommodationPeople as $accommodationPerson) {
                if ($accommodationPerson->getAccommodationGroup()->getAccommodation()->getService() == $service) {
                    $dateInterval = $this->getDateInterval($accommodationPerson->getStartDate(), $accommodationPerson->getEndDate(), $start, $end);
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbAccommodationsPeople;
                }
            }

            $this->datas[$service->getId()] = [
                'name' => $service->getName(),
                'nbAccommodations' => $nbAccommodations,
                'nbPlaces' => $nbPlaces,
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbAccommodations ? ($capacityDays / $interval->format('%a')) : null,
                'averageOccupancy' => $nbAccommodationsPeople ? ($occupancyDays / $interval->format('%a')) : null,
            ];

            $this->nbAccommodations += $nbAccommodations;
            $this->nbPlaces += $nbPlaces;
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbAccommodationsPeople += $nbAccommodationsPeople;
        }

        return [
            'services' => $this->datas,
            'interval' => $interval->format('%a'),
            'nbAccommodations' => $this->nbAccommodations,
            'nbPlaces' => $this->nbPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $this->nbAccommodations ? ($this->capacityDays / $interval->format('%a')) : null,
            'averageOccupancy' => $this->nbAccommodationsPeople ? ($this->occupancyDays / $interval->format('%a')) : null,
        ];
    }

    /**
     * Donne tous les groupes de places d'un service avec leur taux d'occupation.
     */
    public function getOccupancyRateByAccommodation(\DateTime $start, \DateTime $end, Service $service = null): array
    {
        $accommodations = $this->repoAccommodation->findAccommodationsForOccupancy($this->currentUser, $service);
        $accommodationPeople = $this->repoAccommodatioPerson->findAccommodationPeople($this->currentUser, $start, $end, $service);
        $interval = date_diff($start, $end);

        foreach ($accommodations as $accommodation) {
            $capacityDays = 0;
            $nbAccommodationsPeople = 0;
            $occupancyDays = 0;

            $dateInterval = $this->getDateInterval($accommodation->getStartDate(), $accommodation->getEndDate(), $start, $end);
            $capacityDays += $dateInterval->format('%a') * $accommodation->getNbPlaces();

            foreach ($accommodationPeople as $accommodationPerson) {
                if ($accommodationPerson->getAccommodationGroup()->getAccommodation() == $accommodation) {
                    $dateInterval = $this->getDateInterval($accommodationPerson->getStartDate(), $accommodationPerson->getEndDate(), $start, $end);
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbAccommodationsPeople;
                }
            }

            $this->datas[$accommodation->getId()] = [
                'accommodation' => $accommodation,
                'nbPlaces' => $accommodation->getNbPlaces(),
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $capacityDays / $interval->format('%a'),
                'averageOccupancy' => $nbAccommodationsPeople ? ($occupancyDays / $interval->format('%a')) : null,
            ];

            ++$this->nbAccommodations;
            $this->nbPlaces += $accommodation->getNbPlaces();
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbAccommodationsPeople += $nbAccommodationsPeople;
        }

        return [
            'accommodations' => $this->datas,
            'interval' => $interval->format('%a'),
            'nbAccommodations' => $this->nbAccommodations,
            'nbPlaces' => $this->nbPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $this->nbAccommodations ? ($this->capacityDays / $interval->format('%a')) : null,
            'averageOccupancy' => $this->nbAccommodationsPeople ? ($this->occupancyDays / $interval->format('%a')) : null,
        ];
    }

    /**
     * Donne l'intervalle entre 2 dates.
     */
    protected function getDateInterval(\DateTime $startDate1, \DateTime $endDate1 = null, \DateTime $startDate2, \DateTime $endDate2): ?\DateInterval
    {
        $minDate = max($startDate1, $startDate2);
        $maxDate = min($endDate1 ?? $endDate2, $endDate2);

        return date_diff($maxDate, $minDate);
    }
}
