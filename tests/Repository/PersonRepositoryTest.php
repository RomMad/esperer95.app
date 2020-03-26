<?php

namespace App\Tests\Repository;

use App\Entity\Person;
use App\Form\Model\PersonSearch;
use App\Repository\PersonRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var PersonRepository
     */
    protected $repo;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var PersonSearch
     */
    protected $personSearch;


    protected function setUp()
    {
        $this->loadFixtureFiles([
            dirname(__DIR__, 2) . "/fixtures/UserFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/ServiceFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/PoleFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/PersonFixtures.yaml"
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var PersonRepository */
        $this->repo = $this->entityManager->getRepository(Person::class);

        $this->person = $this->repo->findOneBy([
            "firstname" => "John",
            "lastname" => "DOE"
        ]);

        $this->personSearch = $this->getPersonSearch();
    }

    protected function getPersonSearch()
    {
        return (new PersonSearch())
            ->setFirstname("John")
            ->setLastname("DOE")
            ->setBirthdate(new \DateTime("1980-01-01"))
            ->setGender(2)
            ->setPhone("01 00 00 00 00");
    }


    public function testCount()
    {
        // $people = self::$container->get(PersonRepository::class)->count([]);
        $this->assertGreaterThanOrEqual(50, $this->repo->count([]));
    }

    public function testfindPersonById()
    {
        $this->assertNotNull($this->repo->findPersonById($this->person->getId()));
    }

    public function testFindAllPeopleQueryWithoutFilters()
    {
        $query = $this->repo->findAllPeopleQuery(new PersonSearch());
        $this->assertGreaterThanOrEqual(50, count($query->getResult()));
    }

    public function testFindAllPeopleQueryWithFilters()
    {
        $query = $this->repo->findAllPeopleQuery($this->personSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllPeopleQueryWithSearch()
    {
        $query = $this->repo->findAllPeopleQuery(new PersonSearch(), "John");
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindPeopleToExport()
    {
        $this->assertGreaterThanOrEqual(50, count($this->repo->findPeopleToExport(new PersonSearch())));
    }

    public function testFindPeopleByResearch()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findPeopleByResearch("do")));
    }

    public function testFindAllPeople()
    {
        $this->assertGreaterThanOrEqual(50, $this->repo->findAllPeople());
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}