<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\SupportPerson;
use App\Form\Model\AvdlSupportSearch;
use App\Form\Model\HotelSupportSearch;
use App\Form\Model\SupportSearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupportPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportPerson[]    findAll()
 * @method SupportPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportPersonRepository extends ServiceEntityRepository
{
    private $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, SupportPerson::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Trouve les suivis sociaux.
     */
    public function findSupportsQuery(SupportSearch $search): Query
    {
        $query = $this->getSupportsQuery();

        if ($search) {
            $query = $this->filters($query, $search);
        }

        return $query->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Retourne toutes les suivis pour l'export.
     */
    public function findSupportsToExport(?SupportSearch $search = null)
    {
        $query = $this->getSupportsQuery()
            ->leftJoin('sp.accommodationsPerson', 'ap')->addSelect('ap')
            ->leftJoin('ap.accommodationGroup', 'ag')->addSelect('ag')
            ->leftJoin('ag.accommodation', 'a')->addSelect('a');

        if ($search) {
            $query = $this->filters($query, $search);
        }

        return $query->orderBy('sg.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Retourne toutes les suivis d'un service pour l'export.
     */
    public function findSupportsFromServiceToExport($search = null, int $serviceId)
    {
        $query = $this->getSupportsFromServiceQuery()
            ->leftJoin('sg.accommodationGroups', 'ag')->addSelect('PARTIAL ag.{id, accommodation}')
            ->leftJoin('ag.accommodation', 'a')->addSelect('PARTIAL a.{id, name}')

            ->where('sg.service = :service')
            ->setParameter('service', $serviceId);

        if ($search) {
            $query = $this->filters($query, $search);
        }

        return $query->orderBy('sg.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Trouve les suivis sociaux AVDL.
     */
    public function findAvdlSupportsQuery(AvdlSupportSearch $search, int $serviceId): Query
    {
        $query = $this->getSupportsQuery()
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')

            ->where('sg.service = :service')
            ->setParameter('service', $serviceId);

        $query = $this->filters($query, $search);

        if (AvdlSupportSearch::DIAG == $search->getDiagOrSupport()) {
            $query->andWhere('avdl.diagStartDate IS NOT NULL');
        }
        if (AvdlSupportSearch::SUPPORT == $search->getDiagOrSupport()) {
            $query->andWhere('avdl.supportStartDate IS NOT NULL');
        }
        if ($search->getSupportType()) {
            $query->andWhere('avdl.supportType IN (:supportType)')
            ->setParameter('supportType', $search->getSupportType());
        }

        return $query->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Trouve les suivis sociaux hôtel.
     */
    public function findHotelSupportsQuery(HotelSupportSearch $search, int $serviceId): Query
    {
        $query = $this->getSupportsQuery()
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
            ->leftJoin('sg.accommodationGroups', 'ag')->addSelect('PARTIAL ag.{id, accommodation}')
            ->leftJoin('ag.accommodation', 'a')->addSelect('PARTIAL a.{id, name}')

            ->where('sg.service = :service')
            ->setParameter('service', $serviceId);

        $query = $this->filters($query, $search);

        if ($search->getHotels()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getHotels() as $hotel) {
                $orX->add($expr->eq('ag.accommodation', $hotel));
            }
            $query->andWhere($orX);
        }

        if ($search->getLevelSupport()) {
            $query->andWhere('hs.levelSupport IN (:levelSupport)')
            ->setParameter('levelSupport', $search->getLevelSupport());
        }

        return $query->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne les suivis sociaux de la personne.
     */
    public function findSupportsOfPerson(Person $person)
    {
        return $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname, email, phone1}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')

            ->where('sp.person = :person')
            ->setParameter('person', $person)

            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les suivis pour l'export complet.
     */
    public function findSupportsFullToExport($search = null)
    {
        $query = $this->getSupportsQuery()
            ->leftJoin('sp.accommodationsPerson', 'ap')->addSelect('ap')
            ->leftJoin('ap.accommodationGroup', 'ag')->addSelect('ag')
            ->leftJoin('ag.accommodation', 'a')->addSelect('a')

            ->leftJoin('sp.evaluationsPerson', 'ep')->addSelect('ep')
            ->leftJoin('ep.initEvalPerson', 'initEvalPerson')->addSelect('initEvalPerson')
            ->leftJoin('ep.evalJusticePerson', 'evalJusticePerson')->addSelect('evalJusticePerson')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addSelect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'evalBudgetPerson')->addSelect('evalBudgetPerson')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addSelect('evalFamilyPerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addSelect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addSelect('evalSocialPerson')

            ->leftJoin('ep.evaluationGroup', 'eg')->addSelect('eg')
            ->leftJoin('eg.initEvalGroup', 'initEvalGroup')->addSelect('initEvalGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addSelect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addSelect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addSelect('evalHousingGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addSelect('evalSocialGroup');

        return $this->filters($query, $search)

            ->setMaxResults(5000)
            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function countSupportsToExport($search = null)
    {
        $query = $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->select('COUNT(sp.id)');

        return $this->filters($query, $search)

            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getSupportsQuery()
    {
        return $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
            ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}');
    }

    protected function getSupportsFromServiceQuery()
    {
        return $this->getSupportsQuery()
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs');
    }

    /**
     * Filtre la recherche.
     */
    protected function filters($query, $search)
    {
        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        if ($search->getHead()) {
            $query->andWhere('sp.head = :head')
                ->setParameter('head', $search->getHead());
        }

        if ($search->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }

        if ($search->getServices() && $search->getServices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($search->getSubServices() && $search->getSubServices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getSubServices() as $subService) {
                $orX->add($expr->eq('sg.subService', $subService));
            }
            $query->andWhere($orX);
        }

        if ($search->getDevices() && $search->getDevices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        if ($search->getReferents() && $search->getReferents()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

        if ($search->getStatus()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getStatus() as $status) {
                $orX->add($expr->eq('sg.status', $status));
            }
            $query->andWhere($orX);
        }

        $supportDates = $search->getSupportDates();

        if (1 == $supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sg.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sg.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 == $supportDates) {
            if ($search->getStart()) {
                if ($search->getStart()) {
                    $query->andWhere('sg.endDate >= :start')
                        ->setParameter('start', $search->getStart());
                }
                if ($search->getEnd()) {
                    $query->andWhere('sg.endDate <= :end')
                        ->setParameter('end', $search->getEnd());
                }
            }
        }
        if (3 == $supportDates || !$supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sg.endDate >= :start OR sg.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sg.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search->getFamilyTypologies()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getFamilyTypologies() as $familyTypology) {
                $orX->add($expr->eq('g.familyTypology', $familyTypology));
            }
            $query->andWhere($orX);
        }

        if ($search->getNbPeople()) {
            $query->andWhere('sg.nbPeople = :nbPeople')
                ->setParameter('nbPeople', $search->getNbPeople());
        }

        return $query;
    }

    public function countSupportPeople(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('sp')->select('COUNT(sp.id)')
            ->leftJoin('sp.supportGroup', 'sg');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('service' == $key) {
                    $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' == $key) {
                    $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' == $key) {
                    $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('status' == $key) {
                    $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
                if ('startDate' == $key) {
                    $query = $query->andWhere('sg.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' == $key) {
                    $query = $query->andWhere('sg.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
            }
        }

        return (int) $query->getQuery()
            ->getSingleScalarResult();
    }
}
