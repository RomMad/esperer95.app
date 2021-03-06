<?php

namespace App\Service\Export;

use App\Entity\Support\Contribution;
use App\Service\ExportExcel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContributionLightExport extends ExportExcel
{
    protected $router;

    public function __construct(UrlGeneratorInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Exporte les données.
     */
    public function exportData($contributions)
    {
        $arrayData[] = array_keys($this->getDatas($contributions[0]));

        foreach ($contributions as $contribution) {
            $arrayData[] = $this->getDatas($contribution);
        }

        $this->createSheet('export_paiements', 'xlsx', $arrayData, 22);
        $this->addTotalRow();

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(Contribution $contribution): array
    {
        $supportGroup = $contribution->getSupportGroup();
        $person = null;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getHead()) {
                $person = $supportPerson->getPerson();
            }
        }

        return [
            'N° opération' => $contribution->getId(),
            'Nom' => $person ? $person->getFullname() : null,
            'Service' => $supportGroup->getService()->getName(),
            'Type d\'opération' => $contribution->getTypeToString(),
            'PF - Date début de la période' => $this->formatDate($contribution->getStartDate()),
            'PF - Date fin de la période' => $this->formatDate($contribution->getEndDate()),
            'Montant à régler' => $contribution->getToPayAmt(),
            'Montant réglé' => $contribution->getPaidAmt(),
            'Montant restant dû' => $contribution->getStillToPayAmt(),
            'Date de l\'opération' => $this->formatDate($contribution->getPaymentDate()),
            'Mode de règlement' => $contribution->getPaymentType() ? $contribution->getPaymentTypeToString() : null,
            'Commentaire' => $contribution->getComment(),
            'Travailleur social' => $contribution->getCreatedBy()->getFullname(),
        ];
    }
}
