<?php

namespace App\Repository;

use App\Entity\SupportPerson;
use App\Form\Model\SupportGroupSearch;
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
    private $currentUserService;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUserService)
    {
        parent::__construct($registry, SupportPerson::class);

        $this->currentUserService = $currentUserService;
    }

    /**
     * Retourne toutes les places pour l'export.
     *
     * @param SupportGroupSearch $supportGroupSearch
     */
    public function findSupportsToExport(SupportGroupSearch $supportGroupSearch = null)
    {
        $query = $this->getSupportsQuery();

        if ($supportGroupSearch) {
            $query = $this->filter($query, $supportGroupSearch);
        }

        return $query->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    protected function getSupportsQuery()
    {
        return $this->createQueryBuilder('sp')
            ->select('PARTIAL sp.{id, status, startDate, endDate, head, role}')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate}')
            ->leftJoin('p.accommodationPersons', 'ap')->addselect('ap')
            ->leftJoin('ap.accommodationGroup', 'ag')->addselect('ag')
            ->leftJoin('ag.accommodation', 'a')->addselect('a')
            ->leftJoin('sp.supportGroup', 'sg')->addselect('PARTIAL sg.{id}')
            ->leftJoin('sg.groupPeople', 'g')->addselect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addselect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addselect('PARTIAL s.{id, name}')
            ->leftJoin('s.pole', 'pole')->addselect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.device', 'd')->addselect('PARTIAL d.{id, name}');
    }

    /**
     * Filtre la recherche.
     */
    protected function filter($query, SupportGroupSearch $supportGroupSearch)
    {
        if (!$this->currentUserService->isRole('ROLE_SUPER_ADMIN')) {
            // if ($this->currentUserService->isRole("ROLE_ADMIN")) {
            $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $this->currentUserService->getServices());
            // } else {
            //     $query->andWhere("sp.referent = :user")
            //         ->setParameter("user",  $this->currentUserService->getUser());
            // }
        }
        if ($supportGroupSearch->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$supportGroupSearch->getFullname().'%');
        }

        // if ($supportGroupSearch->getBirthdate()) {
        //     $query->andWhere("p.birthdate = :birthdate")
        //         ->setParameter("birthdate", $supportGroupSearch->getBirthdate());
        // }
        // if ($supportGroupSearch->getFamilyTypology()) {
        //     $query->andWhere("g.familyTypology = :familyTypology")
        //         ->setParameter("familyTypology", $supportGroupSearch->getFamilyTypology());
        // }
        // if ($supportGroupSearch->getNbPeople()) {
        //     $query->andWhere("g.nbPeople = :nbPeople")
        //         ->setParameter("nbPeople", $supportGroupSearch->getNbPeople());
        // }
        // if ($supportGroupSearch->getStatus()) {
        //     $query->andWhere("sp.status = :status")
        //         ->setParameter("status", $supportGroupSearch->getStatus());
        // }
        if ($supportGroupSearch->getStatus()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getStatus() as $status) {
                $orX->add($expr->eq('sp.status', $status));
            }
            $query->andWhere($orX);
        }

        $supportDates = $supportGroupSearch->getSupportDates();

        if (1 == $supportDates) {
            if ($supportGroupSearch->getStartDate()) {
                $query->andWhere('sp.startDate >= :startDate')
                    ->setParameter('startDate', $supportGroupSearch->getStartDate());
            }
            if ($supportGroupSearch->getEndDate()) {
                $query->andWhere('sp.startDate <= :endDate')
                    ->setParameter('endDate', $supportGroupSearch->getEndDate());
            }
        }
        if (2 == $supportDates) {
            if ($supportGroupSearch->getStartDate()) {
                if ($supportGroupSearch->getStartDate()) {
                    $query->andWhere('sp.endDate >= :startDate')
                        ->setParameter('startDate', $supportGroupSearch->getStartDate());
                }
                if ($supportGroupSearch->getEndDate()) {
                    $query->andWhere('sp.endDate <= :endDate')
                        ->setParameter('endDate', $supportGroupSearch->getEndDate());
                }
            }
        }
        if (3 == $supportDates || !$supportDates) {
            if ($supportGroupSearch->getStartDate()) {
                $query->andWhere('sp.endDate >= :startDate OR sp.endDate IS NULL')
                    ->setParameter('startDate', $supportGroupSearch->getStartDate());
            }
            if ($supportGroupSearch->getEndDate()) {
                $query->andWhere('sp.startDate <= :endDate')
                    ->setParameter('endDate', $supportGroupSearch->getEndDate());
            }
        }

        if ($supportGroupSearch->getReferent()) {
            $query->andWhere('u = :referent')
                ->setParameter('referent', $supportGroupSearch->getReferent());
        }

        if (count($supportGroupSearch->getServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        return $query;
    }

    public function findSupportsFullToExport($supportGroupSearch = null)
    {
        $query = $this->getSupportsQuery();

        $query = $query->leftJoin('sp.evaluationsPerson', 'ep')->addselect('ep')
            ->leftJoin('ep.initEvalPerson', 'initEvalPerson')->addselect('initEvalPerson')
            ->leftJoin('ep.evalJusticePerson', 'evalJusticePerson')->addselect('evalJusticePerson')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addselect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'evalBudgetPerson')->addselect('evalBudgetPerson')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addselect('evalFamilyPerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addselect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addselect('evalSocialPerson')

            ->leftJoin('ep.evaluationGroup', 'eg')->addselect('eg')
            ->leftJoin('eg.initEvalGroup', 'initEvalGroup')->addselect('initEvalGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addselect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addselect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addselect('evalHousingGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addselect('evalSocialGroup');

        if ($supportGroupSearch->getStatus()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getStatus() as $status) {
                $orX->add($expr->eq('sp.status', $status));
            }
            $query->andWhere($orX);
        }

        $supportDates = $supportGroupSearch->getSupportDates();

        if (1 == $supportDates) {
            if ($supportGroupSearch->getStartDate()) {
                $query->andWhere('sp.startDate >= :startDate')
                    ->setParameter('startDate', $supportGroupSearch->getStartDate());
            }
            if ($supportGroupSearch->getEndDate()) {
                $query->andWhere('sp.startDate <= :endDate')
                    ->setParameter('endDate', $supportGroupSearch->getEndDate());
            }
        }
        if (2 == $supportDates) {
            if ($supportGroupSearch->getStartDate()) {
                if ($supportGroupSearch->getStartDate()) {
                    $query->andWhere('sp.endDate >= :startDate')
                        ->setParameter('startDate', $supportGroupSearch->getStartDate());
                }
                if ($supportGroupSearch->getEndDate()) {
                    $query->andWhere('sp.endDate <= :endDate')
                        ->setParameter('endDate', $supportGroupSearch->getEndDate());
                }
            }
        }
        if (3 == $supportDates || !$supportDates) {
            if ($supportGroupSearch->getStartDate()) {
                $query->andWhere('sp.endDate >= :startDate OR sp.endDate IS NULL')
                    ->setParameter('startDate', $supportGroupSearch->getStartDate());
            }
            if ($supportGroupSearch->getEndDate()) {
                $query->andWhere('sp.startDate <= :endDate')
                    ->setParameter('endDate', $supportGroupSearch->getEndDate());
            }
        }

        if ($supportGroupSearch->getReferent()) {
            $query->andWhere('u = :referent')
                ->setParameter('referent', $supportGroupSearch->getReferent());
        }

        if ($supportGroupSearch->getServices() && count($supportGroupSearch->getServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($supportGroupSearch->getDevices() && count($supportGroupSearch->getDevices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        return $query->setMaxResults(1000)
            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
