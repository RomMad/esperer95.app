<?php

namespace App\Service\Indicators;

use App\Entity\EvalProfPerson;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Entity\SupportPerson;
use App\Entity\EvaluationPerson;

class SocialIndicators
{
    protected $varDatas;
    protected $nbGroups = 0;
    protected $typologyDatas;
    protected $genderDatas;
    protected $roleDatas;

    protected $profStatusDatas;

    public function __construct()
    {
    }

    public function getResults($supportPeople)
    {
        $datas = [];

        $this->typologyDatas = $this->initVar(GroupPeople::FAMILY_TYPOLOGY);
        $this->genderDatas = $this->initVar(Person::GENDER);
        $this->roleDatas = $this->initVar(RolePerson::ROLE);
        $this->profStatusDatas = $this->initVar(EvalProfPerson::PROF_STATUS);
        $this->contractTypeDatas = $this->initVar(EvalProfPerson::CONTRACT_TYPE);

        foreach ($supportPeople as $supportPerson) {
            /** @var SupportPerson */
            $supportPerson = $supportPerson;

            $this->typologyDatas = $this->updateVar(
                $supportPerson,
                $supportPerson->getSupportGroup()->getGroupPeople()->getFamilyTypology(),
                $this->typologyDatas
            );
            $this->genderDatas = $this->updateVar(
                $supportPerson,
                $supportPerson->getPerson()->getGender(),
                $this->genderDatas,
            );
            $this->roleDatas = $this->updateVar(
                $supportPerson,
                $supportPerson->getRole(),
                $this->roleDatas,
            );

            $evaluations = $supportPerson->getEvaluationsPerson();
            /** @var EvaluationPerson */
            $evaluationPerson = $evaluations ? $evaluations[($evaluations->count()) - 1] : new EvaluationPerson();

            $evalProfPerson = $evaluationPerson && $evaluationPerson->getEvalProfPerson() ? $evaluationPerson->getEvalProfPerson() : null;

            $this->profStatusDatas = $this->updateVar(
                $supportPerson,
                $evalProfPerson ? $evalProfPerson->getProfStatus() : null,
                $this->profStatusDatas,
            );

            $this->contractTypeDatas = $this->updateVar(
                $supportPerson,
                $evalProfPerson ? $evalProfPerson->getContractType() : null,
                $this->contractTypeDatas,
            );

            if ($supportPerson->getHead()) {
                ++$this->nbGroups;
            }
        }
        $datas['Typologie familiale'] = $this->typologyDatas;
        $datas['Sexe'] = $this->genderDatas;
        $datas['Rôle'] = $this->roleDatas;
        $datas['Statut professionnel'] = $this->profStatusDatas;
        $datas['Type de contrat de travail'] = $this->profStatusDatas;

        $datas['nbGroups'] = $this->nbGroups;
        $datas['nbPeople'] = count($supportPeople);
        // dump($datas);

        return $datas;
    }

    protected function initVar(array $values)
    {
        $array = [];
        foreach ($values as $key => $value) {
            $array[$key] = [
                'name' => $value,
                'nbGroups' => 0,
                'nbPeople' => 0,
            ];
        }

        return $array;
    }

    protected function updateVar(SupportPerson $supportPerson, ?int $var = null, array $varDatas)
    {
        if ($var == null) {
            $varDatas[99]['nbPeople'] = $varDatas[99]['nbPeople'] + 1;
            $supportPerson->getHead() ? $varDatas[99]['nbGroups'] = $varDatas[99]['nbGroups'] + 1 : null;
        } else {
            $varDatas[$var]['nbPeople'] = $varDatas[$var]['nbPeople'] + 1;
            $supportPerson->getHead() ? $varDatas[$var]['nbGroups'] = $varDatas[$var]['nbGroups'] + 1 : null;
        }

        return $varDatas;
    }
}