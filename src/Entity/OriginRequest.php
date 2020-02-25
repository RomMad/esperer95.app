<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OriginRequestRepository")
 */
class OriginRequest
{
    public const RESULT_PRE_ADMISSION = [
        1 => "Admission",
        2 => "Refus du service",
        3 => "Refus de la personne",
        4 => "Refus autre",
        97 => "Autre",
        98 => "Non concerné",
        99 => "Non renseigné"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $orientationDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $preAdmissionDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $resulPreAdmission;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $decisionDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentPreAdmission;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGroup", inversedBy="originRequest", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="originRequests")
     */
    private $organization;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceType(): ?int
    {
        return $this->serviceType;
    }

    public function setServiceType(?int $serviceType): self
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    public function getOrientationDate(): ?\DateTimeInterface
    {
        return $this->orientationDate;
    }

    public function setOrientationDate(?\DateTimeInterface $orientationDate): self
    {
        $this->orientationDate = $orientationDate;

        return $this;
    }

    public function getPreAdmissionDate(): ?\DateTimeInterface
    {
        return $this->preAdmissionDate;
    }

    public function setPreAdmissionDate(?\DateTimeInterface $preAdmissionDate): self
    {
        $this->preAdmissionDate = $preAdmissionDate;

        return $this;
    }

    public function getResulPreAdmission(): ?int
    {
        return $this->resulPreAdmission;
    }

    public function setResulPreAdmission(?int $resulPreAdmission): self
    {
        $this->resulPreAdmission = $resulPreAdmission;

        return $this;
    }

    public function getDecisionDate(): ?\DateTimeInterface
    {
        return $this->decisionDate;
    }

    public function setDecisionDate(?\DateTimeInterface $decisionDate): self
    {
        $this->decisionDate = $decisionDate;

        return $this;
    }

    public function getCommentPreAdmission(): ?string
    {
        return $this->commentPreAdmission;
    }

    public function setCommentPreAdmission(?string $commentPreAdmission): self
    {
        $this->commentPreAdmission = $commentPreAdmission;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }
}