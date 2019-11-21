<?php

namespace App\Entity;

use App\Utils\Phone;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ServiceRepository")
 */
class Service
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(message="Le nom du service ne doit pas être vide.")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RoleUser", mappedBy="service", cascade={"persist"})
     */
    private $roleUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pole", inversedBy="services", cascade={"persist"})
     */
    private $pole;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGrp", mappedBy="service")
     */
    private $supportGrp;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Email(message="L'adresse email n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Regex(pattern="^0[1-9]([-._/ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $chief;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->roleUser = new ArrayCollection();
        $this->supportGrp = new ArrayCollection();
    }

    public function __toString()
    {
        return strval($this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    /**
     * @return Collection|RoleUser[]
     */
    public function getroleUser(): Collection
    {
        return $this->roleUser;
    }

    public function addRoleUser(RoleUser $roleUser): self
    {
        if (!$this->roleUser->contains($roleUser)) {
            $this->roleUser[] = $roleUser;
            $roleUser->setService($this);
        }

        return $this;
    }

    public function removeRoleUser(RoleUser $roleUser): self
    {
        if ($this->roleUser->contains($roleUser)) {
            $this->roleUser->removeElement($roleUser);
            // set the owning side to null (unless already changed)
            if ($roleUser->getService() === $this) {
                $roleUser->setService(null);
            }
        }

        return $this;
    }

    public function getPole(): ?Pole
    {
        return $this->pole;
    }

    public function setPole(?Pole $pole): self
    {
        $this->pole = $pole;

        return $this;
    }

    /**
     * @return Collection|SupportGrp[]
     */
    public function getSupportGrp(): Collection
    {
        return $this->supportGrp;
    }

    public function addSupportGrp(SupportGrp $supportGrp): self
    {
        if (!$this->supportGrp->contains($supportGrp)) {
            $this->supportGrp[] = $supportGrp;
            $supportGrp->setService($this);
        }

        return $this;
    }

    public function removeSupportGrp(SupportGrp $supportGrp): self
    {
        if ($this->supportGrp->contains($supportGrp)) {
            $this->supportGrp->removeElement($supportGrp);
            // set the owning side to null (unless already changed)
            if ($supportGrp->getService() === $this) {
                $supportGrp->setService(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return Phone::getPhoneFormat($this->phone);
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = Phone::formatPhone($phone);

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getChief(): ?string
    {
        return $this->chief;
    }

    public function setChief(?string $chief): self
    {
        $this->chief = $chief;

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
}