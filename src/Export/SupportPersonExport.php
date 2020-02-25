<?php

namespace App\Export;

use App\Service\Export;
use App\Entity\SupportPerson;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SupportPersonExport
{
    protected $arrayData;

    public function __construct()
    {
        $this->arrayData = [];
    }

    /**
     * Exporte les données
     */
    public function exportData($supports)
    {
        $i = 0;
        foreach ($supports as $supportPerson) {
            if ($i == 0) {
                $arrayData[] = array_keys($this->getDatas($supportPerson));
            }
            $arrayData[] = $this->getDatas($supportPerson);
            $i++;
        }

        $export = new Export("export_suivis", "xlsx", $arrayData, 12.5);

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau
     * @param SupportPerson $supportPerson
     * @return array
     */
    public function getDatas(SupportPerson $supportPerson)
    {
        $person = $supportPerson->getPerson();
        $supportGroup = $supportPerson->getSupportGroup();
        $groupPeople = $supportGroup->getGroupPeople();

        return [
            "N° Suivi groupe" => $supportGroup->getId(),
            "N° Suivi personne" => $supportPerson->getId(),
            "N° Groupe" => $groupPeople->getId(),
            "N° Personne" => $person->getId(),
            "Nom" => $person->getLastname(),
            "Prénom" => $person->getFirstname(),
            "Date de naissance" => $this->formatDate($person->getBirthdate()),
            "Typologie familiale" => $groupPeople->getFamilyTypologyType(),
            "Nb de personnes" => $groupPeople->getNbPeople(),
            "Rôle dans le groupe" => $supportPerson->getRoleList(),
            "DP" => $supportPerson->getHead() ? "Oui" : "Non",
            "Statut" => $supportPerson->getStatusList(),
            "Date début suivi" => $this->formatDate($supportPerson->getStartDate()),
            "Date Fin suivi" => $this->formatDate($supportPerson->getEndDate()),
            "Référent social" => $supportGroup->getReferent()->getFullname(),
            "Service" => $supportGroup->getService()->getName(),
            "Pôle" => $supportGroup->getService()->getPole()->getName(),
        ];
    }

    public function formatDate($date)
    {
        return $date ? Date::PHPToExcel($date->format("Y-m-d")) : null;
    }
}
