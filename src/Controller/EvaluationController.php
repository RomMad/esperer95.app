<?php

namespace App\Controller;

use App\Entity\EvaluationGroup;
use App\Entity\EvaluationPerson;
use App\Entity\InitEvalGroup;
use App\Entity\InitEvalPerson;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Form\Evaluation\EvaluationGroupType;
use App\Repository\EvaluationGroupRepository;
use App\Repository\SupportGroupRepository;
use App\Service\Normalisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EvaluationController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repoSupportGroup;
    private $repo;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, EvaluationGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repo = $repo;
    }

    /**
     * Voir une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation", name="support_evaluation_show", methods="GET|POST")
     */
    public function showEvaluation(int $id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);
        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $evaluationGroup = $this->repo->findEvaluationById($id);

        if (!$evaluationGroup) {
            return $this->createEvaluationGroup($supportGroup);
        }

        $form = ($this->createForm(EvaluationGroupType::class, $evaluationGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateEvaluation($evaluationGroup);
        }

        // dump($evaluationGroup);

        return $this->render('app/evaluation/evaluation.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation_edit", name="support_evaluation_edit", methods="POST")
     */
    public function editEvaluation(int $id, Request $request, Normalisation $normalisation): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $evaluationGroup = $this->repo->findEvaluationById($id);

        $form = ($this->createForm(EvaluationGroupType::class, $evaluationGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateAjax($evaluationGroup);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Crée l'évaluation sociale du groupe.
     */
    protected function createEvaluationGroup(SupportGroup $supportGroup)
    {
        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setDate(new \DateTime());

        $initEvalGroup = (new InitEvalGroup())
            ->setSupportGroup($supportGroup);

        $this->manager->persist($initEvalGroup);

        $supportGroup->setInitEvalGroup($initEvalGroup);
        $evaluationGroup->setInitEvalGroup($supportGroup->getInitEvalGroup());

        $this->manager->persist($evaluationGroup);

        foreach ($supportGroup->getSupportPerson() as $supportPerson) {
            $this->createEvaluationPerson($supportPerson, $evaluationGroup);
        }

        $this->manager->flush();

        return $this->redirectToRoute('support_evaluation_show', ['id' => $supportGroup->getId()]);
    }

    /**
     * Crée l'évaluation sociale d'une personne du groupe.
     */
    protected function createEvaluationPerson(SupportPerson $supportPerson, EvaluationGroup $evaluationGroup)
    {
        $evaluationPerson = (new EvaluationPerson())
            ->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson);

        $initEvalPerson = (new InitEvalPerson())
            ->setSupportPerson($supportPerson);

        $this->manager->persist($initEvalPerson);

        $supportPerson->setInitEvalPerson($initEvalPerson);
        $evaluationPerson->setInitEvalPerson($supportPerson->getInitEvalPerson());

        $this->manager->persist($evaluationPerson);
    }

    /**
     * Met à jour l'évaluation sociale du groupe.
     */
    protected function updateEvaluation(EvaluationGroup $evaluationGroup)
    {
        $now = new \DateTime();

        $evaluationGroup->setUpdatedAt($now);

        $evaluationGroup->getSupportGroup()
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->updateBudgetGroup($evaluationGroup);

        $this->manager->persist($evaluationGroup);
        $this->manager->flush();

        $this->addFlash('success', 'Les modifications sont enregistrées.');
    }

    /**
     * Met à jour l'évaluation sociale du groupe.
     */
    protected function updateAjax(EvaluationGroup $evaluationGroup)
    {
        $now = new \DateTime();

        $evaluationGroup->setUpdatedAt($now);

        $evaluationGroup->getSupportGroup()
                            ->setUpdatedAt($now)
                            ->setUpdatedBy($this->getUser());

        $this->updateBudgetGroup($evaluationGroup);

        $this->manager->persist($evaluationGroup);
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'alert' => 'success',
            'msg' => 'Les modifications sont enregistrées.',
            'date' => $evaluationGroup->getUpdatedAt()->format('d/m/Y à H:i'),
            'user' => $this->getUser()->getFullName(),
        ], 200);
    }

    /**
     * Met à jour le budget du groupe.
     */
    protected function updateBudgetGroup(EvaluationGroup $evaluationGroup)
    {
        $resourcesGroupAmt = 0;
        $chargesGroupAmt = 0;
        $debtsGroupAmt = 0;
        $monthlyRepaymentAmt = 0;
        // Ressources et dettes initiales
        $initResourcesGroupAmt = 0;
        $initDebtsGroupAmt = 0;

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
            if ($evalBudgetPerson) {
                $resourcesGroupAmt += $evalBudgetPerson->getResourcesAmt();
                $chargesGroupAmt += $evalBudgetPerson->getChargesAmt();
                $debtsGroupAmt += $evalBudgetPerson->getDebtsAmt();
                $monthlyRepaymentAmt += $evalBudgetPerson->getMonthlyRepaymentAmt();
            }

            $initEvalPerson = $evaluationPerson->getInitEvalPerson();
            if ($initEvalPerson) {
                $initResourcesGroupAmt += $initEvalPerson->getResourcesAmt();
                $initDebtsGroupAmt += $initEvalPerson->getDebtsAmt();
            }
        }

        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();
        $evalBudgetGroup->setResourcesGroupAmt($resourcesGroupAmt);
        $evalBudgetGroup->setChargesGroupAmt($chargesGroupAmt);
        $evalBudgetGroup->setDebtsGroupAmt($debtsGroupAmt);
        $evalBudgetGroup->setMonthlyRepaymentAmt($monthlyRepaymentAmt);
        $evalBudgetGroup->setBudgetBalanceAmt($resourcesGroupAmt - $chargesGroupAmt - $monthlyRepaymentAmt);
        // Ressources et dettes initiales
        $evaluationGroup->getInitEvalGroup()->setResourcesGroupAmt($initResourcesGroupAmt);
        $evaluationGroup->getInitEvalGroup()->setDebtsGroupAmt($initDebtsGroupAmt);
    }
}
