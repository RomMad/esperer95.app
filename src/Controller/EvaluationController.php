<?php

namespace App\Controller;

use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Entity\EvaluationGroup;
use App\Entity\EvaluationPerson;
use App\Entity\InitEvalGroup;
use App\Entity\InitEvalPerson;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use App\Form\Evaluation\EvaluationGroupType;
use App\Repository\EvaluationGroupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EvaluationController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, EvaluationGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repo = $repo;
    }

    /**
     * Modification d'une évaluation sociale
     * 
     * @Route("/support/{id}/evaluation", name="support_evaluation", methods="GET|POST")
     * @param int $id
     * @param Request $request
     * @return Response
     */

    public function editEvaluation(int $id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);
        $this->denyAccessUnlessGranted("VIEW", $supportGroup);

        $evaluationGroup = $this->repo->findEvaluationById($id);
        if (!$evaluationGroup) {
            return $this->createEvaluationGroup($supportGroup);
        }

        $form = $this->createForm(EvaluationGroupType::class, $evaluationGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateEvaluationGroup($evaluationGroup);
        }
        // Vérifie si erreurs
        if ($form->getErrors(true)) {
            $this->getErrors($form);
        }

        return $this->render("app/evaluation/evaluation.html.twig", [
            "support" => $supportGroup,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Crée l'évaluation sociale du groupe
     *
     * @param SupportGroup $supportGroup
     */
    protected function createEvaluationGroup(SupportGroup $supportGroup)
    {
        $evaluationGroup = new EvaluationGroup();
        $now = new \DateTime();

        $evaluationGroup->setSupportGroup($supportGroup)
            ->setDate($now)
            ->setCreatedAt($now);

        $supportGroup->setInitEvalGroup(new InitEvalGroup());
        $evaluationGroup->setInitEvalGroup($supportGroup->getInitEvalGroup());

        $this->manager->persist($evaluationGroup);

        foreach ($supportGroup->getSupportPerson() as $supportPerson) {
            $this->createEvaluationPerson($supportPerson, $evaluationGroup);
        };

        $this->manager->flush();

        return $this->redirectToRoute("support_evaluation", ["id" => $supportGroup->getId()]);
    }

    /**
     * Crée l'évaluation sociale d'une personne du groupe
     *
     * @param SupportPerson $supportPerson
     * @param EvaluationGroup $evaluationGroup
     */
    protected function createEvaluationPerson(SupportPerson $supportPerson, EvaluationGroup $evaluationGroup)
    {
        $evaluationPerson = new EvaluationPerson();

        $evaluationPerson->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson);

        $supportPerson->setInitEvalPerson(new InitEvalPerson());
        $evaluationPerson->setInitEvalPerson($supportPerson->getInitEvalPerson());

        $this->manager->persist($evaluationPerson);
    }

    /**
     * Met à jour l'évaluation sociale du groupe
     * 
     * @param EvaluationGroup $evaluationGroup
     */
    protected function updateEvaluationGroup(EvaluationGroup $evaluationGroup)
    {
        $evaluationGroup->getSupportGroup()->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->updateBudgetGroup($evaluationGroup);

        $this->manager->persist($evaluationGroup);
        $this->manager->flush();

        $this->addFlash("success", "L'évaluation sociale a été mis à jour.");
    }

    /**
     * Met à jour le budget du groupe
     * 
     * @param EvaluationGroup $evaluationGroup
     */
    protected function  updateBudgetGroup(EvaluationGroup $evaluationGroup)
    {
        $resourcesGroupAmt = 0;
        $chargesGroupAmt = 0;
        $debtsGroupAmt = 0;
        $monthlyRepaymentAmt = 0;

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {

            $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
            if ($evalBudgetPerson) {
                $resourcesGroupAmt += $evalBudgetPerson->getResourcesAmt();
                $chargesGroupAmt += $evalBudgetPerson->getChargesAmt();
                $debtsGroupAmt += $evalBudgetPerson->getDebtsAmt();
                $monthlyRepaymentAmt += $evalBudgetPerson->getMonthlyRepaymentAmt();
            }
        };

        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();
        $evalBudgetGroup->setResourcesGroupAmt($resourcesGroupAmt);
        $evalBudgetGroup->setChargesGroupAmt($chargesGroupAmt);
        $evalBudgetGroup->setDebtsGroupAmt($debtsGroupAmt);
        $evalBudgetGroup->setMonthlyRepaymentAmt($monthlyRepaymentAmt);
        $evalBudgetGroup->setBudgetBalanceAmt($resourcesGroupAmt - $chargesGroupAmt - $monthlyRepaymentAmt);
    }

    protected function getErrors($form)
    {
        foreach ($form->getErrors(true) as $error) {
            $errorOrigin = $error->getOrigin();
            $this->addFlash("danger", $errorOrigin->getName() . " : " . $error->getMessage());
        }
    }
}
