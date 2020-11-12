<?php

namespace App\Repository;

use App\Entity\GroupPeople;
use App\Entity\Referent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Referent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Referent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Referent[]    findAll()
 * @method Referent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referent::class);
    }

    /**
     * Donne tous les groupes de personnes.
     */
    public function findAllReferentsQuery($supportGroupId, $search): Query
    {
        $query = $this->createQueryBuilder('ref')
            ->andWhere('ref.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getName()) {
            $query->andWhere('ref.name LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getSocialWorker()) {
            $query->andWhere('ref.socialWorker LIKE :socialWorker')
                ->setParameter('socialWorker', '%'.$search->getSocialWorker().'%');
        }
        if ($search->getType()) {
            $query->andWhere('ref.type = :type')
                ->setParameter('type', $search->getType());
        }
        $query = $query->orderBy('ref.createdAt', 'DESC');

        return $query->getQuery();
    }

    public function findReferentsOfGroupPeople(GroupPeople $groupPeople)
    {
        return $this->createQueryBuilder('r')->select('r')
            ->where('r.groupPeople = :groupPeople')
            ->setParameter('groupPeople', $groupPeople)
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
