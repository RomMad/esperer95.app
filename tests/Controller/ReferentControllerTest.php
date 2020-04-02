<?php

namespace App\Tests\Controller;

use App\Entity\Referent;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReferentControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Referent */
    protected $referent;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/ReferentFixturesTest.yaml"
        ]);

        $this->createLoggedUser($this->dataFixtures);

        $this->referent = $this->dataFixtures["referent1"];
    }

    public function testNewReferentIsUp()
    {
        $this->client->request("GET", $this->generateUri("referent_new", [
            "id" => $this->dataFixtures["groupPeople"]->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Nouveau service social référent");
    }

    public function testEditReferentIsUp()
    {
        $this->client->request("GET", $this->generateUri("referent_edit", [
            "id" => $this->referent->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->referent->getName());
    }

    public function testDeleteReferent()
    {
        $this->client->request("GET", $this->generateUri("referent_delete", [
            "id" => $this->referent->getId()
        ]));

        $this->client->followRedirect();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Group");
    }
}
