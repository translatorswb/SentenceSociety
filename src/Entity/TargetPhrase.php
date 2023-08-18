<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TargetPhraseRepository")
 */
class TargetPhrase
{
    const VALIDATION_STATE_OPEN = 0;
    const VALIDATION_STATE_POSITIVE = 1;
    const VALIDATION_STATE_NEGATIVE = 2;
    const VALIDATION_STATE_INCONCLUSIVE = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $phrase;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SourcePhrase", inversedBy="translations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $source;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rating", mappedBy="target", orphanRemoval=true)
     */
    private $ratings;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="targetPhrases")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     *
     * 0 = no validation, 1 validated ok, 2 validated not ok, 3 inconclusive (?)
     */
    private $validationState = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $validationScore = 0;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->phrase;
    }

    public function setText(string $phrase): self
    {
        $this->phrase = $phrase;

        return $this;
    }

    public function getSource(): ?SourcePhrase
    {
        return $this->source;
    }

    public function setSource(?SourcePhrase $source): self
    {
        $this->source = $source;

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
            $rating->setTarget($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->contains($rating)) {
            $this->ratings->removeElement($rating);
            // set the owning side to null (unless already changed)
            if ($rating->getTarget() === $this) {
                $rating->setTarget(null);
            }
        }

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidationState()
    {
        return $this->validationState;
    }

    /**
     * @param mixed $validationState
     */
    public function setValidationState($validationState): void
    {
        $this->validationState = $validationState;
    }

    /**
     * @return mixed
     */
    public function getValidationScore()
    {
        return $this->validationScore;
    }

    /**
     * @param mixed $validationScore
     */
    public function setValidationScore($validationScore): void
    {
        $this->validationScore = $validationScore;
    }

    public function getPhrase(): ?string
    {
        return $this->phrase;
    }

    public function setPhrase(string $phrase): self
    {
        $this->phrase = $phrase;

        return $this;
    }
}
