<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupPeopleRepository")
 */
class GroupPeople
{
    public const FAMILY_TYPOLOGY = [
        1 => "Femme seule",
        2 => "Homme seul",
        3 => "Couple sans enfant",
        4 => "Femme seule avec enfant(s)",
        5 => "Homme seul avec enfant(s)",
        6 => "Couple avec enfant(s)",
        7 => "Groupe d'adultes sans enfant",
        8 => "Groupe d'adultes avec enfant(s)",
        9 => "Autre"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotNull(message="La typologie familiale doit être renseignée.")
     * @Assert\Range(min = 1, max = 9, minMessage="La typologie familiale doit être renseignée.",  maxMessage="La typologie familiale doit être renseignée.")
     */
    private $familyTypology;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Range(min = 1, max = 10, minMessage="Le nombre de personnes doit être renseigné.",  maxMessage="Le nombre de personnes doit être renseigné.")
     */
    private $nbPeople;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupPeople")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RolePerson", mappedBy="groupPeople", orphanRemoval=true)
     * @Assert\All(constraints={
     *      @Assert\NotBlank(),
     *      @Assert\NotNull,
     * })  
     * @Assert\Valid
     */
    private $rolePerson;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGrp", mappedBy="groupPeople")
     * @Assert\Valid
     */
    private $supports;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupPeopleUpdated")
     */
    private $updatedBy;

    public function __construct()
    {
        $this->supports = new ArrayCollection();
        $this->rolePerson = new ArrayCollection();
        $this->createdAt = new \Datetime();
        $this->updatedAt = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFamilyTypology(): ?int
    {
        return $this->familyTypology;
    }

    public function getFamilyTypologyType(): string
    {
        return self::FAMILY_TYPOLOGY[$this->familyTypology];
    }

    public function setFamilyTypology(?int $familyTypology): self
    {
        $this->familyTypology = $familyTypology;

        return $this;
    }

    public function getNbPeople(): ?int
    {
        return $this->nbPeople;
    }

    public function setNbPeople(?int $nbPeople): self
    {
        $this->nbPeople = $nbPeople;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return Collection|Supports[]
     */
    public function getSupports(): Collection
    {
        return $this->supports;
    }

    public function addSupport(SupportGrp $support): self
    {
        if (!$this->supports->contains($support)) {
            $this->supports[] = $support;
            $support->setGroupPeople($this);
        }

        return $this;
    }

    public function removeSupport(SupportGrp $support): self
    {
        if ($this->supports->contains($support)) {
            $this->supports->removeElement($support);
            // set the owning side to null (unless already changed)
            if ($support->getGroupPeople() === $this) {
                $support->setGroupPeople(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RolePerson[]
     */
    public function getrolePerson(): Collection
    {
        return $this->rolePerson;
    }

    public function addRolePerson(RolePerson $rolePerson): self
    {
        if (!$this->rolePerson->contains($rolePerson)) {
            $this->rolePerson[] = $rolePerson;
            $rolePerson->setGroupPeople($this);
        }

        return $this;
    }

    public function removeRolePerson(RolePerson $rolePerson): self
    {
        if ($this->rolePerson->contains($rolePerson)) {
            $this->rolePerson->removeElement($rolePerson);
            // set the owning side to null (unless already changed)
            if ($rolePerson->getGroupPeople() === $this) {
                $rolePerson->setGroupPeople(null);
            }
        }

        return $this;
    }
}
