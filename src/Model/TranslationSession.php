<?php

namespace App\Model;

use App\Entity\SourcePhraseAssignment;
use App\Entity\TargetPhraseAssignment;


class TranslationSession implements \Serializable
{
    const SERIALIZE_VERSION = 4;

    private $translatedSourceIds;
    private $skippedSourceIds;
    private $createdTargetIds;

    private $validatedTargetIds;
    private $flaggedTargetIds;
    private $skippedTargetIds;

    private $ratings;

    private $bonusesForRounds;

    private $userId = 0;

    private $userLevel = 1;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function __construct()
    {
        $this->translatedSourceIds = [];
        $this->skippedSourceIds = [];
        $this->createdTargetIds = [];

        $this->validatedTargetIds = [];
        $this->flaggedTargetIds = [];
        $this->skippedTargetIds = [];

        $this->ratings = [];

        $this->bonusesForRounds = [];
    }

    public function skipTargetIds() {
        return array_merge($this->validatedTargetIds, $this->flaggedTargetIds, $this->skippedTargetIds);
    }

    public function skipSourceIds() {
        return array_merge($this->translatedSourceIds, $this->skippedSourceIds);
    }


    public function addTargetFlagId($flagId) {
        $this->flaggedTargetIds[] = $flagId;
    }

    public function addSourcePhraseAssignment($sourceId)
    {
        if (!in_array($sourceId, $this->translatedSourceIds)) {
            $this->translatedSourceIds[] = $sourceId;
        }
    }

    public function addCreatedTargetPhraseAssignment($targetId)
    {
        if (!in_array($targetId, $this->createdTargetIds)) {
            $this->createdTargetIds[] = $targetId;
        }
    }

    public function getCreatedTargetPhraseAssignmentIds() {
        return $this->createdTargetIds;
    }

    public function addValidatedTargetPhraseAssignment($targetId)
    {
        if (!in_array($targetId, $this->validatedTargetIds)) {
            $this->validatedTargetIds[] = $targetId;
        }
    }

    public function addRatingId($id) {
        if (!in_array($id, $this->ratings)) {
            $this->ratings[] = $id;
        }
    }

    public function getRatingIds() {
        return $this->ratings;
    }

    public function addSkipTargetPhraseAssignment($targetId)
    {
        if (!in_array($targetId, $this->skippedTargetIds)) {
            $this->skippedTargetIds[] = $targetId;
        }
    }

    public function rememberBonus(\DateTimeInterface $dateTime) {
        $this->bonusesForRounds[] = ['timestamp' => $dateTime->getTimestamp()];
    }

    public function postponedBonuses() {
        return $this->bonusesForRounds;
    }

    public function clearPostponedBonuses() {
        $this->bonusesForRounds = [];
    }

    /**
     * @return int
     */
    public function getUserLevel(): int
    {
        return $this->userLevel;
    }

    /**
     * @param int $userLevel determines the difficulty of the sentences presented for translation (1/2/3)
     */
    public function setUserLevel(int $userLevel): void
    {
        $this->userLevel = $userLevel;
    }

    public function serialize()
    {
        return serialize([
            self::SERIALIZE_VERSION,
            $this->userId,
            $this->translatedSourceIds,
            $this->skippedSourceIds,
            $this->validatedTargetIds,
            $this->flaggedTargetIds,
            $this->skippedTargetIds,
            $this->ratings,
            $this->bonusesForRounds,
            $this->userLevel,
            $this->createdTargetIds,
        ]);
    }

    public function unserialize($serialized)
    {
        $unserializedList = unserialize($serialized);

        if ($unserializedList[0] == 1) {
            list(
                $version,
                $this->userId,
                $this->translatedSourceIds,
                $this->skippedSourceIds,
                $this->validatedTargetIds,
                $this->flaggedTargetIds,
                $this->skippedTargetIds,
                $this->ratings,
                ) = $unserializedList;
        } else if ($unserializedList[0] == 2) {
            list(
                $version,
                $this->userId,
                $this->translatedSourceIds,
                $this->skippedSourceIds,
                $this->validatedTargetIds,
                $this->flaggedTargetIds,
                $this->skippedTargetIds,
                $this->ratings,
                $this->bonusesForRounds,
                ) = $unserializedList;
        } else if ($unserializedList[0] == 3) {
            list(
                $version,
                $this->userId,
                $this->translatedSourceIds,
                $this->skippedSourceIds,
                $this->validatedTargetIds,
                $this->flaggedTargetIds,
                $this->skippedTargetIds,
                $this->ratings,
                $this->bonusesForRounds,
                $this->userLevel,
                ) = $unserializedList;
        } else if ($unserializedList[0] == 4) {
            list(
                $version,
                $this->userId,
                $this->translatedSourceIds,
                $this->skippedSourceIds,
                $this->validatedTargetIds,
                $this->flaggedTargetIds,
                $this->skippedTargetIds,
                $this->ratings,
                $this->bonusesForRounds,
                $this->userLevel,
                $this->createdTargetIds,
                ) = $unserializedList;
        }
    }
}
