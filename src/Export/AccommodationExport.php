<?php

namespace App\Export;

use App\Service\Export;
use App\Entity\Accommodation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AccommodationExport
{
    protected $arrayData;

    public function __construct()
    {
        $this->arrayData = [];
    }

    /**
     * Exporte les données
     */
    public function exportData($accommodations)
    {
        $arrayData = [];
        $i = 0;

        foreach ($accommodations as $accommodation) {
            if ($i == 0) {
                $arrayData[] = array_keys($this->getDatas($accommodation));
            }
            $arrayData[] = $this->getDatas($accommodation);
            $i++;
        }

        $export = new Export("export_suivis", "xlsx", $arrayData, null);

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau
     * @param Accommodation $accommodation
     * @return array
     */
    public function getDatas(Accommodation $accommodation)
    {
        $numberPeople = 0;
        $today = new \Datetime();

        foreach ($accommodation->getAccommodationGroups() as $accommodationGroup) {
            foreach ($accommodationGroup->getAccommodationPersons() as $accommodationPerson) {
                $endDate = $accommodationPerson->getEndDate();
                if (!$endDate || $endDate->format("d/m/Y") >= $today->format("d/m/Y")) {
                    $numberPeople++;
                }
            }
        }

        return [
            "Nom du groupe de places" => $accommodation->getName(),
            "Service" => $accommodation->getService()->getName(),
            "Dispositif" => $accommodation->getDevice()->getName(),
            "Nombre de places" => $accommodation->getPlacesNumber(),
            "Date d'ouverture" => $this->formatDate($accommodation->getOpeningDate()),
            "Date de fermeture" => $this->formatDate($accommodation->getClosingDate()),
            "Adresse" => $accommodation->getAddress(),
            "Ville" => $accommodation->getCity(),
            "Code postal" => $accommodation->getDepartment(),
            "Type" => $accommodation->getAccommodationType() ? $accommodation->getAccommodationTypeList() : null,
            "Configuration (Diffus ou regroupé)" => $accommodation->getConfiguration() ? $accommodation->getConfigurationList() : null,
            "Individuel ou collectif" => $accommodation->getIndividualCollective() ? $accommodation->getIndividualCollectiveList() : null,
            "Commentaire" => $accommodation->getComment(),
            "Occupation actuelle (Nb de personnes)" => $numberPeople,
        ];
    }

    public function formatDate($date)
    {
        return $date ? Date::PHPToExcel($date->format("Y-m-d")) : null;
    }
}