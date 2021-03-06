<?php

namespace App\Tests\Repository;

use App\Entity\People\Person;
use App\Form\Model\People\PersonSearch;
use App\Repository\People\PersonRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var PersonRepository */
    protected $repo;

    /** @var Person */
    protected $person;

    /** @var PersonSearch */
    protected $search;

    protected function setUp()
    {
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var PersonRepository */
        $this->repo = $this->entityManager->getRepository(Person::class);

        $this->person = $dataFixtures['userRoleUser'];
        $this->search = $this->getPersonSearch();
    }

    protected function getPersonSearch()
    {
        return (new PersonSearch())
            ->setFirstname('John')
            ->setLastname('DOE')
            ->setBirthdate(new \DateTime('1980-01-01'));
    }

    public function testCount()
    {
        // $people = self::$container->get(PersonRepository::class)->count([]);
        $this->assertGreaterThanOrEqual(5, $this->repo->count([]));
    }

    public function testfindPersonById()
    {
        $this->assertNotNull($this->repo->findPersonById($this->person->getId()));
    }

    public function testFindAllPeopleQueryWithoutFilters()
    {
        $query = $this->repo->findPeopleQuery(new PersonSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllPeopleQueryWithFilters()
    {
        $query = $this->repo->findPeopleQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllPeopleQueryWithSearch()
    {
        $query = $this->repo->findPeopleQuery(new PersonSearch(), 'John');
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindPeopleToExport()
    {
        $this->assertGreaterThanOrEqual(5, count($this->repo->findPeopleToExport(new PersonSearch())));
    }

    public function testFindPeopleByResearch()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findPeopleByResearch('do')));
    }

    public function testCountAllPeople()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countPeople());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->person = null;
        $this->search = null;
    }
}
