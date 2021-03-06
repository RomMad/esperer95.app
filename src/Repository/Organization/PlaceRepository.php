<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Admin\OccupancySearch;
use App\Form\Model\Organization\PlaceSearch;
use App\Form\Utils\Choices;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Place|null find($id, $lockMode = null, $lockVersion = null)
 * @method Place|null findOneBy(array $criteria, array $orderBy = null)
 * @method Place[]    findAll()
 * @method Place[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    /**
     * Retourne toutes les places.
     */
    public function findPlacesQuery(PlaceSearch $search = null, CurrentUserService $currentUser = null): Query
    {
        $query = $this->getPlaces();

        if ($currentUser && !$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('pl.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->orderBy('pl.name', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Retourne toutes les places pour l'export.
     *
     * @return mixed
     */
    public function findPlacesToExport(PlaceSearch $search = null)
    {
        $query = $this->getPlaces();

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->orderBy('pl.name', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la liste des groupes de places.
     */
    public function getPlacesQueryBuilder(?int $serviceId = null, ?int $subServiceId = null): QueryBuilder
    {
        $query = $this->createQueryBuilder('pl')
            ->select('PARTIAL pl.{id, name, service, address, city, zipcode, commentLocation, locationId, lat, lon}');

        if ($subServiceId) {
            $query->where('pl.subService = :subService')
            ->setParameter('subService', $subServiceId);
        } else {
            $query->where('pl.service = :service')
            ->setParameter('service', $serviceId);
        }

        return  $query->andWhere('pl.endDate IS NULL')
            ->orWhere('pl.endDate > :date')
            ->setParameter('date', new \Datetime())

            ->orderBy('pl.name', 'ASC');
    }

    /**
     * Donne toutes les places du service.
     *
     * @return Place[]|null
     */
    public function findPlacesOfService(Service $service)
    {
        return $this->createQueryBuilder('pl')->select('pl')
            ->leftJoin('pl.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('pl.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->where('pl.service = :service')
            ->setParameter('service', $service)

            ->orderBy('pl.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les places du sous-service.
     *
     * @return Place[]|null
     */
    public function findPlacesOfSubService(SubService $subService)
    {
        return $this->createQueryBuilder('pl')->select('pl')
            ->innerJoin('pl.device', 'd')->addSelect('PARTIAL d.{id,name}')

            ->where('pl.subService = :subService')
            ->setParameter('subService', $subService)

            ->orderBy('pl.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les groupes de places pour les taux d'occupation.
     *
     * @return Place[]|null
     */
    public function findPlacesForOccupancy(OccupancySearch $search, $currentUser, Service $service = null, SubService $subService = null): array
    {
        $query = $this->createQueryBuilder('pl')->select('pl')
            ->innerJoin('pl.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('pl.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->innerJoin('pl.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->andWhere('pl.endDate > :start OR pl.endDate IS NULL')->setParameter('start', $search->getStart())
            ->andWhere('pl.startDate < :end')->setParameter('end', $search->getEnd());

        if ($search->getPole()) {
            $query = $query->andWhere('s.pole = :pole')
                ->setParameter('pole', $search->getPole());
        }

        if ($service) {
            $query->andWhere('pl.service = :service')
                ->setParameter('service', $service);
        }
        if ($subService) {
            $query->andWhere('pl.subService = :subService')
                ->setParameter('subService', $subService);
        }
        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('pl.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query
            ->addOrderBy('pl.service', 'ASC')
            ->addOrderBy('pl.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne le groupe de places actuel du suivi.
     */
    public function findCurrentPlaceOfSupport(SupportGroup $supportGroup): ?Place
    {
        return $this->createQueryBuilder('pl')->select('PARTIAL pl.{id, rentAmt}')
            ->leftJoin('pl.placeGroups', 'pg')->addSelect('PARTIAL pg.{id, supportGroup}')
            ->andWhere('pg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)
            ->orderBy('pg.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    protected function getPlaces()
    {
        return $this->createQueryBuilder('pl')->select('pl')
            ->leftJoin('pl.device', 'd')->addSelect('PARTIAL d.{id,name}')
            ->leftJoin('pl.service', 's')->addSelect('PARTIAL s.{id,name}')
            ->leftJoin('pl.subService', 'ss')->addSelect('PARTIAL ss.{id,name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id,name}')
            ->leftJoin('pl.placeGroups', 'pg')->addSelect('PARTIAL pg.{id,startDate, endDate}')
            ->leftJoin('pg.placePeople', 'pp')->addSelect('PARTIAL pp.{id,startDate, endDate}');
        // ->leftJoin("pp.person", "p")->addSelect("PARTIAL p.{id,firstname, lastname}");
    }

    /**
     * Filtre la recherche.
     */
    protected function filter($query, PlaceSearch $search)
    {
        if ($search->getName()) {
            $query->andWhere('pl.name LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getCity()) {
            $query->andWhere('pl.city LIKE :city')
                ->setParameter('city', '%'.$search->getCity().'%');
        }
        if ($search->getNbPlaces()) {
            $query->andWhere('pl.nbPlaces = :nbPlaces')
                ->setParameter('nbPlaces', $search->getNbPlaces());
        }

        $supportDates = $search->getSupportDates();

        if (1 === $supportDates) {
            if ($search->getStart()) {
                $query->andWhere('pl.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('pl.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 === $supportDates) {
            if ($search->getStart()) {
                if ($search->getStart()) {
                    $query->andWhere('pl.endDate >= :start')
                        ->setParameter('start', $search->getStart());
                }
                if ($search->getEnd()) {
                    $query->andWhere('pl.endDate <= :end')
                        ->setParameter('end', $search->getEnd());
                }
            }
        }
        if (3 === $supportDates || !$supportDates) {
            if ($search->getStart()) {
                $query->andWhere('pl.endDate >= :start OR pl.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('pl.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search->getPole()) {
            $query->andWhere('pole.id = :pole_id')
                ->setParameter('pole_id', $search->getPole());
        }

        if (Choices::DISABLED === $search->getDisabled()) {
            $query->andWhere('pl.disabledAt IS NOT NULL');
        } elseif (Choices::ACTIVE === $search->getDisabled()) {
            $query->andWhere('pl.disabledAt IS NULL');
        }

        if ($search->getPoles() && count($search->getPoles())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getPoles() as $pole) {
                $orX->add($expr->eq('s.pole', $pole));
            }
            $query->andWhere($orX);
        }

        if ($search->getServices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('s.id', $service));
            }
            $query->andWhere($orX);
        }
        if ($search->getSubServices() && $search->getSubServices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getSubServices() as $subService) {
                $orX->add($expr->eq('ss.id', $subService));
            }
            $query->andWhere($orX);
        }
        if ($search->getDevices() && $search->getDevices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('d.id', $device));
            }
            $query->andWhere($orX);
        }

        return $query;
    }
}
