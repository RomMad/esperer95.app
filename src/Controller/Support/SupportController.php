<?php

namespace App\Controller\Support;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\EntityManager\SupportCollections;
use App\EntityManager\SupportDuplicator;
use App\EntityManager\SupportManager;
use App\Event\Support\SupportGroupEvent;
use App\Form\Model\Support\SupportSearch;
use App\Form\Model\Support\SupportsInMonthSearch;
use App\Form\Support\Support\NewSupportGroupType;
use App\Form\Support\Support\SupportCoefficientType;
use App\Form\Support\Support\SupportGroupType;
use App\Form\Support\Support\SupportSearchType;
use App\Form\Support\Support\SupportsInMonthSearchType;
use App\Form\Utils\Choices;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\Support\ContributionRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Calendar;
use App\Service\Grammar;
use App\Service\Pagination;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Liste des suivis sociaux.
     *
     * @Route("/supports", name="supports", methods="GET|POST")
     */
    public function viewListSupports(Request $request, SupportManager $supportManager, SupportPersonRepository $repo, Pagination $pagination): Response
    {
        $form = ($this->createForm(SupportSearchType::class, $search = new SupportSearch()))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $supportManager->exportData($search, $repo);
        }

        return $this->render('app/support/support/listSupports.html.twig', [
            'form' => $form->createView(),
            'supports' => $pagination->paginate($repo->findSupportsQuery($search), $request),
        ]);
    }

    /**
     * Nouveau suivi social.
     *
     * @Route("/group/{id}/support/new", name="support_new", methods="GET|POST")
     */
    public function newSupportGroup(
        int $id,
        PeopleGroupRepository $repoPeopleGroup,
        Request $request,
        SupportManager $supportManager,
        ServiceRepository $repoService,
        EventDispatcherInterface $dispatcher
    ): Response {
        $peopleGroup = $repoPeopleGroup->findPeopleGroupById($id);
        $supportGroup = $supportManager->getNewSupportGroup($peopleGroup, $request, $repoService);

        $form = ($this->createForm(SupportGroupType::class, $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $supportGroup->getAgreement()) {
            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if ($supportManager->create($peopleGroup, $supportGroup)) {
                $this->addFlash('success', 'Le suivi social est créé.');

                if ($form->get('cloneSupport')->getViewData() != null) {
                    $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support_group.duplicator');
                }

                if ($supportGroup->getStartDate() && Choices::YES === $supportGroup->getService()->getPlace()
                    && Choices::YES === $supportGroup->getDevice()->getPlace()) {
                    return $this->redirectToRoute('support_place_new', ['id' => $supportGroup->getId()]);
                }

                return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
            }
            $this->addFlash('danger', 'Attention, un suivi social est déjà en cours pour '.(
                count($peopleGroup->getPeople()) > 1 ? 'ce ménage.' : 'cette personne.'));
        }

        return $this->render('app/support/support/supportGroupEdit.html.twig', [
            'people_group' => $peopleGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Donne le formulaire pour créer un nouveau suivi social au groupe (via AJAX).
     *
     * @Route("/group/{id}/new_support", name="people_group_new_support", methods="GET")
     */
    public function newSupportGroupAjax(PeopleGroup $peopleGroup, SupportPersonRepository $repoSupportPerson)
    {
        $supportGroup = (new SupportGroup())->setReferent($this->getUser());

        $nbSupports = $repoSupportPerson->countSupportsOfPeople($peopleGroup);

        $form = $this->createForm(NewSupportGroupType::class, $supportGroup, [
            'action' => $this->generateUrl('support_new', ['id' => $peopleGroup->getId()]),
        ]);

        return $this->json([
            'code' => 200,
            'data' => [
                'form' => $this->render('app/support/support/formNewSupport.html.twig', [
                    'form' => $form->createView(),
                    'nbSupports' => $nbSupports,
                ]),
            ],
        ]);
    }

    /**
     * Donne le formulaire pour éditer suivi social au groupe (via AJAX).
     *
     * @Route("/support/change_service", name="support_change_service", methods="GET|POST")
     */
    public function getSupportGroupType(Request $request)
    {
        $form = $this->createForm(NewSupportGroupType::class, new SupportGroup())
            ->handleRequest($request);

        return $this->render('app/support/support/formSupportGroup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un suivi social.
     *
     * @Route("/support/{id}/edit", name="support_edit", methods="GET|POST")
     */
    public function editSupportGroup(int $id, Request $request, SupportGroupRepository $repoSupportGroup, SupportManager $supportManager): Response
    {
        $supportGroup = $repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm(SupportGroupType::class, $supportGroup))
        ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supportManager->update($supportGroup);

            return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
        }

        $formCoeff = ($this->createForm(SupportCoefficientType::class, $supportGroup))
            ->handleRequest($request);

        if ($this->isGranted('ROLE_ADMIN') && $formCoeff->isSubmitted() && $formCoeff->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Le coefficient du suivi est mis à jour.');

            return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
        }

        return $this->render('app/support/support/supportGroupEdit.html.twig', [
            'form' => $form->createView(),
            'formCoeff' => $formCoeff->createView(),
        ]);
    }

    /**
     * Voir un suivi social.
     *
     * @Route("/support/{id}/view", name="support_view", methods="GET")
     */
    public function viewSupportGroup(int $id, SupportManager $supportManager, SupportCollections $supportCollections, EventDispatcherInterface $dispatcher): Response
    {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support_group.check');

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        return $this->render('app/support/support/supportGroupView.html.twig', [
            'support' => $supportGroup,
            'referents' => $supportCollections->getReferents($supportGroup),
            'nbNotes' => $supportCollections->getNbNotes($supportGroup),
            'nbRdvs' => $nbRdvs = $supportCollections->getNbRdvs($supportGroup),
            'nbDocuments' => $supportCollections->getNbDocuments($supportGroup),
            'nbContributions' => $supportCollections->getNbContributions($supportGroup),
            'lastRdv' => $nbRdvs ? $supportCollections->getLastRdvs($supportGroup) : null,
            'nextRdv' => $nbRdvs ? $supportCollections->getNextRdvs($supportGroup) : null,
            'evaluation' => $supportCollections->getEvaluation($supportGroup),
        ]);
    }

    /**
     * Supprime le suivi social du groupe.
     *
     * @Route("/support/{id}/delete", name="support_delete", methods="GET")
     * @IsGranted("DELETE", subject="supportGroup")
     */
    public function deleteSupportGroup(SupportGroup $supportGroup): Response
    {
        $this->manager->getFilters()->disable('softdeleteable');
        $this->manager->remove($supportGroup);
        $this->manager->flush();

        $this->addFlash('warning', 'Le suivi social est supprimé.');

        $peopleGroup = $supportGroup->getPeopleGroup();

        (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->deleteItem(PeopleGroup::CACHE_GROUP_SUPPORTS_KEY.$peopleGroup->getId());

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Ajout de personnes au suivi.
     *
     * @Route("/support/{id}/add_people", name="support_add_people", methods="GET")
     */
    public function addPeopleInSupport(SupportGroup $supportGroup, SupportManager $supportManager, EvaluationGroupRepository $repoEvaluationGroup): Response
    {
        if (!$supportManager->addPeopleInSupport($supportGroup, $repoEvaluationGroup)) {
            $this->addFlash('warning', "Aucune personne n'a été ajoutée au suivi.");
        }

        $this->discache($supportGroup);

        return $this->redirectToRoute('support_edit', [
            'id' => $supportGroup->getId(),
        ]);
    }

    /**
     * Retire la personne du suivi social.
     *
     * @Route("/supportGroup/{id}/remove-{support_pers_id}/{_token}", name="remove_support_pers", methods="GET")
     * @ParamConverter("supportPerson", options={"id" = "support_pers_id"})
     */
    public function removeSupportPerson(SupportGroup $supportGroup, string $_token, SupportPerson $supportPerson): Response
    {
        // Vérifie si le token est valide avant de retirer la personne du suivi social
        if ($this->isCsrfTokenValid('remove'.$supportPerson->getId(), $_token)) {
            // Vérifie si la personne est le demandeur principal
            if ($supportPerson->getHead()) {
                return $this->json([
                    'code' => 200,
                    'action' => null,
                    'alert' => 'danger',
                    'msg' => 'Le demandeur principal ne peut pas être retiré du suivi.',
                    'data' => null,
                ], 200);
            }

            try {
                $supportGroup->removeSupportPerson($supportPerson);
                $supportGroup->setNbPeople($supportGroup->getSupportPeople()->count());
                $this->manager->flush();

                $this->discache($supportGroup);

                return $this->json([
                'code' => 200,
                'action' => 'delete',
                'alert' => 'warning',
                'msg' => $supportPerson->getPerson()->getFullname().' est retiré'.Grammar::gender($supportPerson->getPerson()->getGender()).' du suivi social.',
                'data' => null,
            ], 200);
            } catch (\Throwable $th) {
            }
        }

        return $this->getErrorMessage();
    }

    /**
     * Affiche les suivis dans le mois.
     *
     * @Route("/supports/this_month", name="supports_current_month", methods="GET|POST")
     * @Route("/supports/{year}/{month}", name="supports_in_month", methods="GET|POST", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * })
     */
    public function showSupportsWithContribution(int $year = null, int $month = null, Request $request, SupportGroupRepository $repoSupportGroup, ContributionRepository $repoContribution, Pagination $pagination): Response
    {
        $search = new SupportsInMonthSearch();
        if (User::STATUS_SOCIAL_WORKER === $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = ($this->createForm(SupportsInMonthSearchType::class, $search))
            ->handleRequest($request);

        // if ($month === null) {
        //     $month = (new \DateTime())->modify('-1 month')->format('n');
        // }

        $calendar = new Calendar($year, $month);

        $supports = $pagination->paginate($repoSupportGroup->findSupportsBetween($calendar->getFirstDayOfTheMonth(), $calendar->getLastDayOfTheMonth(), $search), $request, 30);

        $supportsId = [];
        foreach ($supports->getItems() as $support) {
            $supportsId[] = $support->getId();
        }

        return $this->render('app/support/support/supportsInMonthWithContributions.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
            'supports' => $supports,
            'contributions' => $repoContribution->findContributionsBetween(
                $calendar->getFirstDayOfTheMonth(),
                $calendar->getLastDayOfTheMonth(),
                $supportsId
            ),
        ]);
    }

    /**
     * Crée une copie d'un suivi social.
     *
     * @Route("/support/{id}/clone", name="support_clone", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function cloneSupport(SupportGroup $supportGroup, SupportDuplicator $supportDuplicator): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        if ($supportDuplicator->duplicate($supportGroup)) {
            $this->manager->flush();

            $this->discache($supportGroup);

            $this->addFlash('success', 'Les informations du précédent suivi ont été ajoutées (évaluation sociale, documents...)');
        } else {
            $this->addFlash('warning', 'Aucun autre suivi n\'a été trouvé.');
        }

        return $this->redirectToRoute('support_view', [
            'id' => $supportGroup->getId(),
        ]);
    }

    /**
     * Supprime le cache du suivi social et de l'évaluation sociale.
     */
    public function discache(SupportGroup $supportGroup): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $id = $supportGroup->getId();

        if ($supportGroup->getReferent()) {
            $cache->deleteItem(User::CACHE_USER_SUPPORTS_KEY.$supportGroup->getReferent()->getId());
        }

        return $cache->deleteItems([
            SupportGroup::CACHE_SUPPORT_KEY.$id,
            SupportGroup::CACHE_FULLSUPPORT_KEY.$id,
            EvaluationGroup::CACHE_EVALUATION_KEY.$id,
        ]);
    }
}
