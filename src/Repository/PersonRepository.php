<?php

namespace App\Repository;

use App\Entity\Person;
use App\Form\Model\PersonSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * Donne le groupe de personnes.
     */
    public function findPersonById(int $id): ?Person
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.createdBy', 'createdBy')->addselect('PARTIAL createdBy.{id, firstname, lastname}')
            ->leftJoin('p.updatedBy', 'updatedBy')->addselect('PARTIAL updatedBy.{id, firstname, lastname}')
            ->leftJoin('p.rolesPerson', 'r')->addselect('PARTIAL r.{id, role, head}')
            ->leftJoin('r.groupPeople', 'g')->addselect('PARTIAL g.{id, familyTypology, nbPeople, createdAt, updatedAt}')
            ->leftJoin('p.supports', 'sp')->addselect('PARTIAL sp.{id, status, startDate, endDate, updatedAt}')
            ->leftJoin('sp.supportGroup', 'sg')->addselect('PARTIAL sg.{id}')
            ->leftJoin('sg.referent', 'ref')->addselect('PARTIAL ref.{id, firstname, lastname, email, phone1}')
            ->leftJoin('sg.service', 's')->addselect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.device', 'd')->addselect('PARTIAL d.{id, name}')
            ->leftJoin('s.pole', 'pole')->addselect('PARTIAL pole.{id, name}')

            ->andWhere('p.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Retourne toutes les personnes.
     */
    public function findAllPeopleQuery(PersonSearch $personSearch, string $search = null): Query
    {
        $query = $this->createQueryBuilder('p')
            ->select('p');
        if ($search) {
            $query->Where("CONCAT(p.lastname,' ' ,p.firstname) LIKE :search")
                ->setParameter('search', '%'.$search.'%');
        }
        if ($personSearch->getFirstname()) {
            $query->andWhere('p.firstname LIKE :firstname')
                ->setParameter('firstname', $personSearch->getFirstname().'%');
        }
        if ($personSearch->getLastname()) {
            $query->andWhere('p.lastname LIKE :lastname')
                ->setParameter('lastname', $personSearch->getLastname().'%');
        }

        if ($personSearch->getBirthdate()) {
            $query->andWhere('p.birthdate = :birthdate')
                ->setParameter('birthdate', $personSearch->getBirthdate());
        }
        if ($personSearch->getGender()) {
            $query->andWhere('p.gender = :gender')
                ->setParameter('gender', $personSearch->getGender());
        }
        if ($personSearch->getPhone()) {
            $query->andWhere('p.phone1 = :phone OR p.phone2 = :phone')
                ->setParameter('phone', $personSearch->getPhone());
        }

        return $query->orderBy('p.lastname', 'ASC')
            ->getQuery();
    }

    /**
     * Trouve toutes les personnes à exporter.
     *
     * @return mixed
     */
    public function findPeopleToExport(PersonSearch $personSearch)
    {
        $query = $this->findAllPeopleQuery($personSearch);

        return $query->getResult();
    }

    /**
     *  Trouve toutes les personnes.
     *
     * @return mixed
     */
    public function findPeopleByResearch(string $search = null)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->Where("CONCAT(p.lastname,' ' ,p.firstname) LIKE :search OR CONCAT(p.firstname,' ' ,p.lastname) LIKE :search")
            ->setParameter('search', '%'.$search.'%')
            ->orderBy('p.lastname, p.firstname', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findAllPeople(array $criteria = null)
    {
        $query = $this->createQueryBuilder('p')->select('COUNT(p.id)');

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
