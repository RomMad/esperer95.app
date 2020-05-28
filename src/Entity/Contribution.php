<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ContributionRepository;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ContributionRepository::class)
 * @UniqueEntity(
 *     fields={"contribDate", "type", "supportGroup"},
 *     message="Une redevance existe déjà pour ce mois."
 * )
 */
class Contribution
{
    use CreatedUpdatedEntityTrait;

    public const CONTRIBUTION_TYPE = [
        1 => 'Redevance/ Participation',
        2 => 'Caution',
        // 97 => 'Autre',
    ];

    public const DEFAULT_CONTRIBUTION_TYPE = 1;

    public const PAYMENT_TYPE = [
        1 => 'Virement automatique',
        2 => 'Virement mensuel',
        3 => 'Chèque',
        4 => 'Espèce',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Column(type="date")
     * @Groups("get")
     */
    private $contribDate;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("get")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $salaryAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $resourcesAmt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get", "export"})
     */
    private $credential;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $contribAmt;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"get", "export"})
     */
    private $paymentDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("get")
     */
    private $paymentType;

    /**
     * @Groups("export")
     */
    private $paymentTypeToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $paymentAmt = 0;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $stillDueAmt = 0;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"get", "export"})
     */
    private $comment;

    /**
     * @ORM\Column(type="smallint")
     * @Groups("get")
     */
    private $type = self::DEFAULT_CONTRIBUTION_TYPE;

    /**
     * @Groups("export")
     */
    private $typeToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $returnAmt;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"get", "export"})
     */
    private $returnDate;

    /**
     * @ORM\ManyToOne(targetEntity=SupportGroup::class, inversedBy="contributions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContribDate(): ?\DateTimeInterface
    {
        return $this->contribDate;
    }

    public function setContribDate(?\DateTimeInterface $contribDate): self
    {
        $this->contribDate = $contribDate;

        return $this;
    }

    public function getSalaryAmt(): ?float
    {
        return $this->salaryAmt;
    }

    public function setSalaryAmt(?float $salaryAmt): self
    {
        $this->salaryAmt = $salaryAmt;

        return $this;
    }

    public function getResourcesAmt(): ?float
    {
        return $this->resourcesAmt;
    }

    public function setResourcesAmt(?float $resourcesAmt): self
    {
        $this->resourcesAmt = $resourcesAmt;

        return $this;
    }

    public function getCredential(): ?string
    {
        return $this->credential;
    }

    public function setCredential(?string $credential): self
    {
        $this->credential = $credential;

        return $this;
    }

    public function getContribAmt(): ?float
    {
        return $this->contribAmt;
    }

    public function setContribAmt(?float $contribAmt): self
    {
        $this->contribAmt = $contribAmt;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getPaymentType(): ?int
    {
        return $this->paymentType;
    }

    public function getPaymentTypeToString(): ?string
    {
        return $this->paymentType ? self::PAYMENT_TYPE[$this->paymentType] : null;
    }

    public function setPaymentType(?int $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getPaymentAmt(): ?float
    {
        return $this->paymentAmt;
    }

    public function setPaymentAmt(?float $paymentAmt): self
    {
        $this->paymentAmt = $paymentAmt;

        return $this;
    }

    public function getStillDueAmt(): ?float
    {
        return $this->getContribAmt() - $this->getPaymentAmt();
    }

    public function setStillDueAmt(?float $stillDueAmt = null): self
    {
        $this->stillDueAmt = $this->getStillDueAmt();

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getTypeToString(): ?string
    {
        return $this->type ? self::CONTRIBUTION_TYPE[$this->type] : null;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getReturnAmt(): ?float
    {
        return $this->returnAmt;
    }

    public function setReturnAmt(?float $returnAmt): self
    {
        $this->returnAmt = $returnAmt;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate(?\DateTimeInterface $returnDate): self
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }
}