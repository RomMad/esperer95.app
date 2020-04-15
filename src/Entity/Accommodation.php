<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\LocationEntityTrait;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccommodationRepository")
 * @UniqueEntity(
 *     fields={"name", "service"},
 *     errorPath="name",
 *     message="Ce groupe de places existe déjà !")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Accommodation
{
    use CreatedUpdatedEntityTrait;
    use LocationEntityTrait;
    use SoftDeleteableEntity;

    public const ACCOMMODATION_TYPE = [
        1 => 'Chambre individuelle',
        2 => 'Chambre collective',
        3 => "Chambre d'hôtel",
        4 => 'Dortoir',
        5 => 'Logement T1',
        6 => 'Logement T2',
        7 => 'Logement T3',
        8 => 'Logement T4',
        9 => 'Logement T5',
        10 => 'Logement T6',
        11 => 'Logement T7',
        12 => 'Logement T8',
        13 => 'Logement T9',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const CONFIGURATION = [
        1 => 'Diffus',
        2 => 'Regroupé',
    ];

    public const INDIVIDUAL_COLLECTIVE = [
        1 => 'Individuel',
        2 => 'Collectif',
        97 => 'Autre',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $placesNumber;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotNull()
     */
    private $openingDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $closingDate;

    /**
     * @ORM\Column(name="department", length=10, nullable=true)
     */
    private $zipCode; // NE PAS SUPPRIMER

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $accommodationType;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $configuration;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $individualCollective;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="accommodations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Device", inversedBy="accommodations")
     */
    private $device;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccommodationGroup", mappedBy="accommodation", orphanRemoval=true)
     */
    private $accommodationGroups;

    // /**
    //  * @ORM\OneToMany(targetEntity="App\Entity\AccommodationPerson", mappedBy="accommodation")
    //  */
    // private $accommodationPeople;

    public function __construct()
    {
        $this->accommodationGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->getService()->getName().' - '.$this->name;
    }

    public function getPlacesNumber(): ?int
    {
        return $this->placesNumber;
    }

    public function setPlacesNumber(?int $placesNumber): self
    {
        $this->placesNumber = $placesNumber;

        return $this;
    }

    public function getAccommodationType(): ?int
    {
        return $this->accommodationType;
    }

    /**
     * @Groups("export")
     */
    public function getAccommodationTypeToString(): ?string
    {
        return self::ACCOMMODATION_TYPE[$this->accommodationType];
    }

    public function setAccommodationType(?int $accommodationType): self
    {
        $this->accommodationType = $accommodationType;

        return $this;
    }

    public function getConfiguration(): ?int
    {
        return $this->configuration;
    }

    public function setConfiguration(?int $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @Groups("export")
     */
    public function getConfigurationToString(): ?string
    {
        return self::CONFIGURATION[$this->configuration];
    }

    public function getIndividualCollective(): ?int
    {
        return $this->individualCollective;
    }

    /**
     * @Groups("export")
     */
    public function getIndividualCollectiveToString(): ?string
    {
        return self::INDIVIDUAL_COLLECTIVE[$this->individualCollective];
    }

    public function setIndividualCollective(?int $individualCollective): self
    {
        $this->individualCollective = $individualCollective;

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

    public function getOpeningDate(): ?\DateTimeInterface
    {
        return $this->openingDate;
    }

    public function setOpeningDate(?\DateTimeInterface $openingDate): self
    {
        $this->openingDate = $openingDate;

        return $this;
    }

    public function getClosingDate(): ?\DateTimeInterface
    {
        return $this->closingDate;
    }

    public function setClosingDate(?\DateTimeInterface $closingDate): self
    {
        $this->closingDate = $closingDate;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): self
    {
        $this->device = $device;

        return $this;
    }

    /**
     * @return Collection|AccommodationGroup[]
     */
    public function getAccommodationGroups(): ?Collection
    {
        return $this->accommodationGroups;
    }

    public function addAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if (!$this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups[] = $accommodationGroup;
            $accommodationGroup->setAccommodation($this);
        }

        return $this;
    }

    public function removeAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if ($this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups->removeElement($accommodationGroup);
            // set the owning side to null (unless already changed)
            if ($accommodationGroup->getAccommodation() === $this) {
                $accommodationGroup->setAccommodation(null);
            }
        }

        return $this;
    }
}
