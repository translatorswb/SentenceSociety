<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SourcePhraseRepository")
 */
class SourcePhrase
{
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
     * @ORM\OneToMany(targetEntity="App\Entity\TargetPhrase", mappedBy="source", orphanRemoval=true)
     */
    private $translations;

    /**
     * @ORM\Column(type="integer")
     */
    private $level = 1;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
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

    /**
     * @return Collection|TargetPhrase[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(TargetPhrase $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setSource($this);
        }

        return $this;
    }

    public function removeTranslation(TargetPhrase $translation): self
    {
        if ($this->translations->contains($translation)) {
            $this->translations->removeElement($translation);
            // set the owning side to null (unless already changed)
            if ($translation->getSource() === $this) {
                $translation->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level): void
    {
        $this->level = $level;
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
