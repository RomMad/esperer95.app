<?php

namespace App\Form\Model;

use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;

class HotelSupportSearch
{
    use DateSearchTrait;
    use ReferentServiceDeviceSearchTrait;

    public const SUPPORT_DATES = [
        1 => 'Début du suivi',
        2 => 'Fin du suivi',
        3 => 'Période de suivi',
    ];

    /**
     * @var string|null
     */
    private $fullname;

    /**
     * @var array
     */
    private $familyTypologies;

    /**
     * @var array
     */
    private $status;

    /**
     * @var int|null
     */
    private $supportDates;

    /**
     * @var bool
     */
    private $export;

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getFamilyTypologies(): ?array
    {
        return $this->familyTypologies;
    }

    public function setFamilyTypologies(?array $familyTypologies): self
    {
        $this->familyTypologies = $familyTypologies;

        return $this;
    }

    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function setStatus(?array $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSupportDates(): ?int
    {
        return $this->supportDates;
    }

    public function setSupportDates(int $supportDates): self
    {
        $this->supportDates = $supportDates;

        return $this;
    }

    public function getExport(): ?bool
    {
        return $this->export;
    }

    public function setExport(bool $export): self
    {
        $this->export = $export;

        return $this;
    }
}