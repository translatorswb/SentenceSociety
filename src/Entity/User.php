<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=512, nullable=true, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenDate;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $country;
    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $personalName;

    /**
     * @ORM\Column(type="integer")
     */
    private $rank = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $points = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $verifiedEmail = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AwardedPoints", mappedBy="user", orphanRemoval=true)
     */
    private $awardedPoints;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TargetPhrase", mappedBy="user")
     */
    private $targetPhrases;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rating", mappedBy="user")
     */
    private $ratings;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    public function __construct()
    {
        $this->awardedPoints = new ArrayCollection();
        $this->targetPhrases = new ArrayCollection();
        $this->ratings = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->name;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank): void
    {
        $this->rank = $rank;
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param mixed $points
     */
    public function setPoints($points): void
    {
        $this->points = $points;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|AwardedPoints[]
     */
    public function getAwardedPoints(): Collection
    {
        return $this->awardedPoints;
    }

    public function addAwardedPoint(AwardedPoints $awardedPoint): self
    {
        if (!$this->awardedPoints->contains($awardedPoint)) {
            $this->awardedPoints[] = $awardedPoint;
            $awardedPoint->setUser($this);
        }

        return $this;
    }

    public function removeAwardedPoint(AwardedPoints $awardedPoint): self
    {
        if ($this->awardedPoints->contains($awardedPoint)) {
            $this->awardedPoints->removeElement($awardedPoint);
            // set the owning side to null (unless already changed)
            if ($awardedPoint->getUser() === $this) {
                $awardedPoint->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TargetPhrase[]
     */
    public function getTargetPhrases(): Collection
    {
        return $this->targetPhrases;
    }

    public function addTargetPhrase(TargetPhrase $targetPhrase): self
    {
        if (!$this->targetPhrases->contains($targetPhrase)) {
            $this->targetPhrases[] = $targetPhrase;
            $targetPhrase->setUser($this);
        }

        return $this;
    }

    public function removeTargetPhrase(TargetPhrase $targetPhrase): self
    {
        if ($this->targetPhrases->contains($targetPhrase)) {
            $this->targetPhrases->removeElement($targetPhrase);
            // set the owning side to null (unless already changed)
            if ($targetPhrase->getUser() === $this) {
                $targetPhrase->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setUser($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->contains($rating)) {
            $this->ratings->removeElement($rating);
            // set the owning side to null (unless already changed)
            if ($rating->getUser() === $this) {
                $rating->setUser(null);
            }
        }

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenDate(): ?\DateTimeInterface
    {
        return $this->tokenDate;
    }

    public function setTokenDate(?\DateTimeInterface $tokenDate): self
    {
        $this->tokenDate = $tokenDate;

        return $this;
    }

    public function getVerifiedEmail(): ?bool
    {
        return $this->verifiedEmail;
    }

    public function setVerifiedEmail(bool $verifiedEmail): self
    {
        $this->verifiedEmail = $verifiedEmail;

        return $this;
    }
    public function setPersonalName(?string $personalName): self
    {
        $this->personalName = $personalName;

        return $this;
    }

    public function getPersonalName(): ?string
    {
        return $this->personalName;
    }

}
