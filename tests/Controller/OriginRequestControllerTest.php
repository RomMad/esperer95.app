<?php

namespace App\Tests\Controller;

use App\Entity\OriginRequest;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OriginRequestControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/SupportFixturesTest.yaml",
        ]);

        $this->createLoggedUser($this->dataFixtures);

        $this->supportGroup = $this->dataFixtures["supportGroup1"];
    }

    public function testEditOriginRequestIsUp()
    {
        $this->client->request("POST", $this->generateUri("support_originRequest", [
            "id" => $this->supportGroup->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Origine de la demande");
    }
}
