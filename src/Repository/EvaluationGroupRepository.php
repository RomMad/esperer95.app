<?php

namespace App\Repository;

use App\Entity\EvaluationGroup;
use App\Entity\SupportGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvaluationGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationGroup[]    findAll()
 * @method EvaluationGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationGroup::class);
    }

    /**
     * Donne toute l'évaluation sociale du groupe.
     */
    public function findEvaluationById(int $supportGroupId): ?EvaluationGroup
    {
        return $this->createQueryBuilder('eg')->select('eg')
            ->join('eg.supportGroup', 'sg')->addselect('PARTIAL sg.{id}')
            ->join('sg.groupPeople', 'gp')->addselect('PARTIAL gp.{id, familyTypology, nbPeople}')

            ->leftJoin('eg.evaluationPeople', 'ep')->addselect('ep')
            ->join('ep.supportPerson', 'sp')->addselect('PARTIAL sp.{id, person, head, role}')
            ->join('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')

            ->leftJoin('eg.initEvalGroup', 'initEvalGroup')->addselect('initEvalGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addselect('evalSocialGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addselect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addselect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addselect('evalHousingGroup')

            ->leftJoin('ep.initEvalPerson', 'initEvalPerson')->addselect('initEvalPerson')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addselect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'evalBudgetPerson')->addselect('evalBudgetPerson')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addselect('evalFamilyPerson')
            ->leftJoin('ep.evalJusticePerson', 'evalJusticePerson')->addselect('evalJusticePerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addselect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addselect('evalSocialPerson')

            ->andWhere('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId)

            ->orderBy('p.birthdate', 'ASC')
            // ->setMaxResults(1)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne les ressources.
     */
    public function findEvaluationResourceById(int $supportGroupId): ?EvaluationGroup
    {
        return $this->createQueryBuilder('eg')->select('eg')
            ->join('eg.supportGroup', 'sg')->addselect('PARTIAL sg.{id}')
            ->leftJoin('eg.evaluationPeople', 'ep')->addselect('ep')
            ->leftJoin('eg.evalBudgetGroup', 'ebg')->addselect('PARTIAL ebg.{id, contributionAmt}')
            ->leftJoin('ep.evalBudgetPerson', 'ebp')->addselect('PARTIAL ebp.{id, resourcesAmt, salaryAmt}')

            ->andWhere('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne toute l'évaluation sociale du groupe.
     */
    public function findLastEvaluationFromSupport(SupportGroup $supportGroup): ?EvaluationGroup
    {
        return $this->createQueryBuilder('eg')->select('eg')

            ->andWhere('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)

            ->orderBy('eg.id', 'DESC')
            ->setMaxResults(1)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
