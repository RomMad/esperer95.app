<?php

namespace App\EntityManager;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\InitEvalGroup;
use App\Entity\Organization\Service;
use App\Entity\Support\SupportGroup;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Evaluation\EvaluationPersonRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use Doctrine\ORM\EntityManagerInterface;

class SupportDuplicator
{
    private $manager;

    private $repoSupportGroup;
    private $repoSupportPerson;
    private $repoEvaluationGroup;
    private $repoEvaluationPerson;
    private $repoNote;
    private $repoDocument;

    /** @var SupportGroup|null */
    private $supportGroup = null;
    /** @var EvaluationGroup|null */
    private $evaluationGroup = null;

    public function __construct(
        EntityManagerInterface $manager,
        SupportGroupRepository $repoSupportGroup,
        SupportPersonRepository $repoSupportPerson,
        EvaluationGroupRepository $repoEvaluationGroup,
        EvaluationPersonRepository $repoEvaluationPerson,
        NoteRepository $repoNote,
        DocumentRepository $repoDocument
    ) {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoSupportPerson = $repoSupportPerson;
        $this->repoEvaluationGroup = $repoEvaluationGroup;
        $this->repoEvaluationPerson = $repoEvaluationPerson;
        $this->repoNote = $repoNote;
        $this->repoDocument = $repoDocument;
    }

    public function duplicate(SupportGroup $supportGroup)
    {
        $this->supportGroup = $supportGroup;

        if ($this->duplicateSupportGroup($supportGroup)) {
            return $supportGroup;
        }
        if ($this->duplicateSupportPeople($supportGroup)) {
            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if ($this->evaluationGroup && 0 === $supportPerson->getEvaluationsPerson()->count()) {
                    $evaluationPerson = (new EvaluationPerson())
                        ->setEvaluationGroup($supportGroup->getEvaluationsGroup()->last())
                        ->setSupportPerson($supportPerson);

                    $this->manager->persist($evaluationPerson);
                }
            }

            return $this->evaluationGroup;
        }

        return null;
    }

    /**
     * Crée une copie d'un suivi social.
     */
    public function duplicateSupportGroup(SupportGroup $supportGroup): ?SupportGroup
    {
        $lastSupportGroup = $this->repoSupportGroup->findLastSupport($supportGroup);

        if (null === $lastSupportGroup) {
            return null;
        }

        $lastEvaluation = $this->repoEvaluationGroup->findEvaluationOfSupport($lastSupportGroup->getId());
        $documents = $this->repoDocument->findBy(['supportGroup' => $lastSupportGroup]);
        $lastNote = $this->repoNote->findOneBy(['supportGroup' => $lastSupportGroup], ['updatedAt' => 'DESC']);

        if ($lastEvaluation && 0 === $supportGroup->getEvaluationsGroup()->count()) {
            $evaluationGroup = (clone $lastEvaluation)->setSupportGroup($supportGroup);
            $supportGroup->getEvaluationsGroup()->add($evaluationGroup);
        }

        foreach ($documents as $document) {
            $newDocument = (clone $document)->setSupportGroup($supportGroup);
            $supportGroup->getDocuments()->add($newDocument);
        }

        if ($lastNote) {
            $note = (clone $lastNote)->setSupportGroup($supportGroup);
            $supportGroup->getNotes()->add($note);
        }

        return $supportGroup;
    }

    private function duplicateSupportPeople(SupportGroup $supportGroup)
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $lastSupportPerson = $this->repoSupportPerson->findLastSupport($supportPerson);

            if (null === $lastSupportPerson) {
                continue;
            }

            $lastEvaluationPerson = $this->repoEvaluationPerson->findEvaluationOfSupportPerson($lastSupportPerson->getId());

            if (0 === $supportGroup->getEvaluationsGroup()->count() && null === $this->evaluationGroup) {
                $this->evaluationGroup = $this->cloneEvaluationGroup($lastEvaluationPerson->getEvaluationGroup());
            } else {
                $this->evaluationGroup = $supportGroup->getEvaluationsGroup()->last();
            }

            if ($lastEvaluationPerson && 0 === $supportPerson->getEvaluationsPerson()->count()) {
                $evaluationPerson = (clone $lastEvaluationPerson)->setSupportPerson($supportPerson);
                $supportPerson->getEvaluationsPerson()->add($evaluationPerson);
                $evaluationPerson->setEvaluationGroup($this->evaluationGroup);

                if ($lastEvaluationPerson->getInitEvalPerson()) {
                    $intiEvalPerson = clone $lastEvaluationPerson->getInitEvalPerson();
                    $intiEvalPerson->setSupportPerson($supportPerson);
                    $evaluationPerson->setInitEvalPerson($intiEvalPerson);
                }
            }
        }

        if ($this->evaluationGroup) {
            $this->evaluationGroup->setSupportGroup($this->supportGroup);
            $supportGroup->addEvaluationGroup($this->evaluationGroup);
        }
        // dd($this->evaluationGroup);

        return $supportGroup;
    }

    /**
     * Clone the evaluation of the group.
     */
    private function cloneEvaluationGroup(EvaluationGroup $evaluationGroup): EvaluationGroup
    {
        $newEvaluationGroup = new EvaluationGroup();
        $newEvaluationGroup->setDate(new \DateTime())
            ->setInitEvalGroup(new InitEvalGroup());

        if ($evaluationGroup->getInitEvalGroup()) {
            $newEvaluationGroup->setInitEvalGroup(clone $evaluationGroup->getInitEvalGroup());
        }
        if ($evaluationGroup->getEvalBudgetGroup()) {
            $newEvaluationGroup->setEvalBudgetGroup(clone $evaluationGroup->getEvalBudgetGroup());
        }
        if ($evaluationGroup->getEvalFamilyGroup()) {
            $newEvaluationGroup->setEvalFamilyGroup(clone $evaluationGroup->getEvalFamilyGroup());
        }
        if ($evaluationGroup->getEvalHousingGroup()) {
            $newEvaluationGroup->setEvalHousingGroup(clone $evaluationGroup->getEvalHousingGroup());
        }
        if ($evaluationGroup->getEvalSocialGroup()) {
            $newEvaluationGroup->setEvalSocialGroup(clone $evaluationGroup->getEvalSocialGroup());
        }
        if (Service::SERVICE_PASH_ID && $this->supportGroup->getService()->getId() && $evaluationGroup->getEvalHotelLifeGroup()) {
            $newEvaluationGroup->setEvalHotelLifeGroup(clone $evaluationGroup->getEvalHotelLifeGroup());
        }

        return $newEvaluationGroup;
    }
}