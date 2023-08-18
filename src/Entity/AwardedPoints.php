<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AwardedPointsRepository")
 */
class AwardedPoints
{
    const TYPE_OTHER = 'other';
    const TYPE_SIGNED_UP = 'signed-up';
    const TYPE_CONSECUTIVE_DAY = 'consecutive-day';
    const TYPE_TRANSLATION_ROUND = 'translation-round';

    const VALUE_SIGNED_UP = 0;
    const VALUE_CONSECUTIVE_DAY = 0;
    const VALUE_TRANSLATION_ROUND = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="awardedPoints")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $type;

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
