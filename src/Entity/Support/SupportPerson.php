<?php

namespace App\Entity\Support;

use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\InitEvalPerson;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Form\Utils\Choices;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\SupportPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class SupportPerson
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $role;

    /**
     * @Groups("export")
     */
    private $roleToString;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $head;

    /**
     * @Groups("export").
     */
    private $headToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(message="La date de début ne doit pas être vide.")
     * @Groups("export")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $endDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="Le statut doit être renseigné.")
     * @Assert\Range(min = 1, max = 5, minMessage="Le statut doit être renseigné.",  maxMessage="Le statut doit être renseigné.")
     */
    private $status;

    /**
     * @Groups("export")
     */
    private $statusToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endStatus;

    /**
     * @Groups("export")
     */
    private $endStatusToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("export")
     */
    private $endStatusComment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\People\Person", inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportGroup", inversedBy="supportPeople")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\Note", mappedBy="supportPerson")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Evaluation\EvaluationPerson", mappedBy="supportPerson", cascade={"persist", "remove"})
     */
    private $evaluationsPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\InitEvalPerson", mappedBy="supportPerson", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $initEvalPerson;

    /**
     * @ORM\OneToMany(targetEntity=PlacePerson::class, mappedBy="supportPerson")
     */
    private $placesPerson;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->evaluationsPerson = new ArrayCollection();
        $this->placesPerson = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function getRoleToString(): ?string
    {
        return $this->role ? RolePerson::ROLE[$this->role] : null;
    }

    public function setRole(?int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getHead(): ?bool
    {
        return $this->head;
    }

    public function getHeadToString(): ?string
    {
        return Choices::YES_NO_BOOLEAN[$this->head];
    }

    public function setHead(?bool $head): self
    {
        $this->head = $head;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getStatusToString(): ?string
    {
        return $this->status ? SupportGroup::STATUS[$this->status] : null;
    }

    public function getStatusHotelToString(): ?string
    {
        return $this->status ? HotelSupport::STATUS[$this->status] : null;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEndStatus(): ?int
    {
        return $this->endStatus;
    }

    public function setEndStatus(?int $endStatus): self
    {
        $this->endStatus = $endStatus;

        return $this;
    }

    public function getEndStatusToString(): ?string
    {
        return $this->endStatus ? SupportGroup::END_STATUS[$this->endStatus] : null;
    }

    public function getEndStatusComment(): ?string
    {
        return $this->endStatusComment;
    }

    public function setEndStatusComment(?string $endStatusComment): self
    {
        $this->endStatusComment = $endStatusComment;

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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

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

    /**
     * @return Note[]|Collection|null
     */
    public function getNotes()
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setSupportPerson($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getSupportPerson() === $this) {
                $note->setSupportPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return EvaluationPerson[]|Collection|null
     */
    public function getEvaluationsPerson()
    {
        return $this->evaluationsPerson;
    }

    public function addEvaluationsPerson(EvaluationPerson $evaluationsPerson): self
    {
        if (!$this->evaluationsPerson->contains($evaluationsPerson)) {
            $this->evaluationsPerson[] = $evaluationsPerson;
            $evaluationsPerson->setSupportPerson($this);
        }

        return $this;
    }

    public function removeEvaluationsPerson(EvaluationPerson $evaluationsPerson): self
    {
        if ($this->evaluationsPerson->contains($evaluationsPerson)) {
            $this->evaluationsPerson->removeElement($evaluationsPerson);
            // set the owning side to null (unless already changed)
            if ($evaluationsPerson->getSupportPerson() === $this) {
                $evaluationsPerson->setSupportPerson(null);
            }
        }

        return $this;
    }

    public function getInitEvalPerson(): ?InitEvalPerson
    {
        return $this->initEvalPerson;
    }

    public function setInitEvalPerson(?InitEvalPerson $initEvalPerson): self
    {
        $this->initEvalPerson = $initEvalPerson;

        // set the owning side of the relation if necessary
        if ($this !== $initEvalPerson->getSupportPerson()) {
            $initEvalPerson->setSupportPerson($this);
        }

        return $this;
    }

    /**
     * @return PlacePerson[]|Collection|null
     */
    public function getPlacesPerson()
    {
        return $this->placesPerson;
    }

    public function addPlacesPerson(PlacePerson $placesPerson): self
    {
        if (!$this->placesPerson->contains($placesPerson)) {
            $this->placesPerson[] = $placesPerson;
            $placesPerson->setSupportPerson($this);
        }

        return $this;
    }

    public function removePlacesPerson(PlacePerson $placesPerson): self
    {
        if ($this->placesPerson->contains($placesPerson)) {
            $this->placesPerson->removeElement($placesPerson);
            // set the owning side to null (unless already changed)
            if ($placesPerson->getSupportPerson() === $this) {
                $placesPerson->setSupportPerson(null);
            }
        }

        return $this;
    }
}
