<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TargetPhraseFlagRepository")
 */
class TargetPhraseFlag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TargetPhrase")
     * @ORM\JoinColumn(nullable=false)
     */
    private $targetPhrase;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTargetPhrase(): ?TargetPhrase
    {
        return $this->targetPhrase;
    }

    public function setTargetPhrase(?TargetPhrase $targetPhrase): self
    {
        $this->targetPhrase = $targetPhrase;

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
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp): void
    {
        $this->timestamp = $timestamp;
    }
}
