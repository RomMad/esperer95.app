<?php

namespace App\Controller\App;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Form\Admin\OccupancySearchType;
use App\Form\Model\Admin\OccupancySearch;
use App\Service\Indicators\OccupancyIndicators;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OccupancyRateController extends AbstractController
{
    /**
     * Taux d'occupation des dispositifs.
     *
     * @Route("/occupancy/devices", name="occupancy_devices", methods="GET|POST")
     * @Route("/occupancy/service/{id}/devices", name="occupancy_service_devices", methods="GET|POST")
     */
    public function showOccupancyByDevice(Service $service = null, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $search = $this->getOccupancySearch($request);

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/occupancy/occupancyByDevice.html.twig', [
            'service' => $service,
            'search' => $search,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateByDevice($search, $service),
        ]);
    }

    /**
     * Taux d'occupation des services.
     *
     * @Route("/occupancy/services", name="occupancy_services", methods="GET|POST")
     * @Route("/occupancy/device/{id}/services", name="occupancy_device_services", methods="GET|POST")
     */
    public function showOccupancyByService(Device $device = null, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $search = $this->getOccupancySearch($request);

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/occupancy/occupancyByService.html.twig', [
            'device' => $device,
            'search' => $search,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateByService($search, $device),
        ]);
    }

    /**
     * Taux d'occupation des services.
     *
     * @Route("/occupancy/service/{id}/sub_services", name="occupancy_sub_services", methods="GET|POST")
     */
    public function showOccupancyBySubService(Service $service, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $search = $this->getOccupancySearch($request);

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/occupancy/occupancyBySubService.html.twig', [
            'service' => $service,
            'search' => $search,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateBySubService($search, $service),
        ]);
    }

    /**
     * Taux d'occupation des groupes de place.
     *
     * @Route("/occupancy/service/{id}/accommodations", name="occupancy_service_accommodations", methods="GET|POST")
     * @Route("/occupancy/accommodations", name="occupancy_accommodations", methods="GET|POST")
     */
    public function showOccupancyServiceByAccommodation(Service $service = null, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $search = $this->getOccupancySearch($request);

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/occupancy/occupancyByAccommodation.html.twig', [
            'service' => $service,
            'search' => $search,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateByAccommodation($search, $service),
        ]);
    }

    /**
     * Taux d'occupation des groupes de place.
     *
     * @Route("/occupancy/sub_services/{id}/accommodations", name="occupancy_sub_service_accommodations", methods="GET|POST")
     */
    public function showOccupancySubServiceByAccommodation(SubService $subService, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $search = $this->getOccupancySearch($request);

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/occupancy/occupancySubServiceByAccommodation.html.twig', [
            'subService' => $subService,
            'search' => $search,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateByAccommodation($search, null, $subService),
        ]);
    }

    protected function getOccupancySearch(Request $request)
    {
        $search = new OccupancySearch();
        $today = new \DateTime('today');

        if ($request->query->get('start') && $request->query->get('end')) {
            $search->setStart(new \DateTime($request->query->get('start')))
                ->setEnd(new \DateTime($request->query->get('end')));
        } else {
            $search->setStart((new \DateTime('today'))->modify('-1 day'));
        }

        if (null === $search->getStart()) {
            $search->setStart(new \DateTime($today->format('Y').'-01-01'));
        }

        if (null === $search->getEnd()) {
            $search->setEnd($today);
        }

        return $search;
    }
}
