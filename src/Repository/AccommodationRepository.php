<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\ORM\Query;
use App\Entity\Accommodation;
use App\Form\Model\AccommodationSearch;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Accommodation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Accommodation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Accommodation[]    findAll()
 * @method Accommodation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccommodationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Accommodation::class);
    }

    /**
     * Retourne toutes les places

     * @param AccommodationSearch $accommodationSearch
     * @return Query
     */
    public function findAllAccommodationsQuery(AccommodationSearch $accommodationSearch): Query
    {
        $query =  $this->getAccommodations();

        if ($accommodationSearch) {
            $query = $this->filter($query, $accommodationSearch);
        }

        return $query->orderBy("a.name", "ASC")
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }


    /**
     * Retourne toutes les places pour l'export
     *
     * @param AccommodationSearch $accommodationSearch
     */
    public function findAccommodationsToExport(AccommodationSearch $accommodationSearch = null)
    {
        $query =  $this->getAccommodations();

        if ($accommodationSearch) {
            $query = $this->filter($query, $accommodationSearch);
        }

        return $query->orderBy("a.name", "DESC")
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /** 
     * Donne la liste des groupes de places
     */
    public function getAccommodationsQueryList($service)
    {
        $query =  $this->createQueryBuilder("a")
            ->select("PARTIAL a.{id, name, service}")

            ->where("a.service = :service")
            ->setParameter("service", $service)

            ->andWhere("a.closingDate IS NULL")
            ->orWhere("a.closingDate > :date")
            ->setParameter("date", new \Datetime());

        return $query->orderBy("a.name", "ASC");
    }

    /**
     * Donne toutes les places du service
     *
     * @return Service|null
     */
    public function findAccommodationsFromService(Service $service)
    {
        return $this->createQueryBuilder("a")
            ->select("a")
            ->innerJoin("a.device", "d")->addSelect("PARTIAL d.{id,name}")

            ->where("a.service = :service")
            ->setParameter("service", $service)

            ->orderBy("a.name", "ASC")

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    protected function getAccommodations()
    {
        return $this->createQueryBuilder("a")->select("a")
            ->innerJoin("a.device", "d")->addSelect("PARTIAL d.{id,name}")
            ->innerJoin("a.service", "s")->addSelect("PARTIAL s.{id,name}")
            ->innerJoin("s.pole", "pole")->addSelect("PARTIAL pole.{id,name}")
            ->leftJoin("a.accommodationGroups", "ag")->addSelect("PARTIAL ag.{id,startDate, endDate}")
            ->leftJoin("ag.accommodationPersons", "ap")->addSelect("PARTIAL ap.{id,startDate, endDate}");
        // ->leftJoin("ap.person", "p")->addSelect("PARTIAL p.{id,firstname, lastname}");
    }

    /**
     * Filtre la recherche
     *
     * @param AccommodationSearch $accommodationSearch
     */
    protected function filter($query, AccommodationSearch $accommodationSearch)
    {
        if ($accommodationSearch->getName()) {
            $query->andWhere("a.name LIKE :name")
                ->setParameter("name", '%' . $accommodationSearch->getName() . '%');
        }
        if ($accommodationSearch->getCity()) {
            $query->andWhere("a.city LIKE :city")
                ->setParameter("city", '%' . $accommodationSearch->getCity() . '%');
        }
        if ($accommodationSearch->getPlacesNumber()) {
            $query->andWhere("a.placesNumber = :placesNumber")
                ->setParameter("placesNumber", $accommodationSearch->getPlacesNumber());
        }

        $supportDates = $accommodationSearch->getSupportDates();

        if ($supportDates == 1) {
            if ($accommodationSearch->getStartDate()) {
                $query->andWhere("a.startDate >= :startDate")
                    ->setParameter("startDate", $accommodationSearch->getStartDate());
            }
            if ($accommodationSearch->getEndDate()) {
                $query->andWhere("a.startDate <= :endDate")
                    ->setParameter("endDate", $accommodationSearch->getEndDate());
            }
        }
        if ($supportDates == 2) {
            if ($accommodationSearch->getStartDate()) {
                if ($accommodationSearch->getStartDate()) {
                    $query->andWhere("a.endDate >= :startDate")
                        ->setParameter("startDate", $accommodationSearch->getStartDate());
                }
                if ($accommodationSearch->getEndDate()) {
                    $query->andWhere("a.endDate <= :endDate")
                        ->setParameter("endDate", $accommodationSearch->getEndDate());
                }
            }
        }
        if ($supportDates == 3 || !$supportDates) {
            if ($accommodationSearch->getStartDate()) {
                $query->andWhere("a.endDate >= :startDate OR a.endDate IS NULL")
                    ->setParameter("startDate", $accommodationSearch->getStartDate());
            }
            if ($accommodationSearch->getEndDate()) {
                $query->andWhere("a.startDate <= :endDate")
                    ->setParameter("endDate", $accommodationSearch->getEndDate());
            }
        }

        if ($accommodationSearch->getPole()) {
            $query->andWhere("p.id = :pole_id")
                ->setParameter("pole_id", $accommodationSearch->getPole());
        }

        if ($accommodationSearch->getService()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($accommodationSearch->getService() as $service) {
                $orX->add($expr->eq("s.id", $service));
            }
            $query->andWhere($orX);
        }

        if ($accommodationSearch->getDevice() && $accommodationSearch->getDevice()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($accommodationSearch->getDevice() as $device) {
                $orX->add($expr->eq("d.id", $device));
            }
            $query->andWhere($orX);
        }
        return $query;
    }
}
