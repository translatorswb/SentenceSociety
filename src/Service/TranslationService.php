<?php

namespace App\Service;


use App\Entity\Rating;
use App\Entity\SourcePhrase;
use App\Entity\SourcePhraseAssignment;
use App\Entity\SourcePhraseFlag;
use App\Entity\TargetPhrase;
use App\Entity\TargetPhraseAssignment;
use App\Entity\TargetPhraseFlag;
use App\Model\TranslationSession;
use App\Repository\SourcePhraseAssignmentRepository;
use App\Repository\SourcePhraseRepository;
use App\Repository\TargetPhraseAssignmentRepository;
use App\Repository\TargetPhraseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TranslationService
{
    /** @var SourcePhraseRepository */
    private $sourcePhraseRepository;

    /** @var TargetPhraseRepository */
    private $targetPhraseRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManagerInterface */
    private $em;

    /** @var ITimeService */
    private $timeService;

    public function __construct(
        SourcePhraseRepository $sourcePhraseRepository,
        TargetPhraseRepository $targetPhraseRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        ITimeService $timeService
    )
    {
        $this->sourcePhraseRepository = $sourcePhraseRepository;
        $this->targetPhraseRepository = $targetPhraseRepository;
        $this->userRepository = $userRepository;
        $this->timeService = $timeService;

        $this->em = $em;
        $this->logger = $logger;
    }

    public function getNextSource(TranslationSession $translationSession):?SourcePhrase
    {
        /** @var SourcePhrase $next */
        $next = $this->sourcePhraseRepository->getNextAvailable($translationSession->skipSourceIds(), $translationSession->getUserLevel());

        if ($next === null) {
            return null;
        } else {

            return $next;
        }
    }

    public function skipAndNextSource(TranslationSession $translationSession, $sourceId): ?SourcePhrase
    {
        // store
        $translationSession->addSourcePhraseAssignment($sourceId);

        /** @var SourcePhrase $next */
        $next = $this->sourcePhraseRepository->getNextAvailable($translationSession->skipSourceIds(), $translationSession->getUserLevel());

        if ($next === null) {
            return null;
        } else {
            // store?
//            $translationSession->addSourcePhraseAssignment($next->getId());
            return $next;
        }
    }

    public function addTranslationForSource(TranslationSession $translationSession, $sourceId, $text):?TargetPhrase {
        $sourcePhrase = $this->sourcePhraseRepository->find($sourceId);

        if ($sourcePhrase !== null) {

            // store as 'translated'
            $translationSession->addSourcePhraseAssignment($sourceId);

            $translation = new TargetPhrase();
            $translation->setText($text);
            $translation->setTimestamp($this->timeService->currentDateTime());
            $sourcePhrase->addTranslation($translation);
            if ($translationSession->getUserId()) {
                $translation->setUser($this->userRepository->find($translationSession->getUserId()));
            }
            $this->em->persist($translation);
            $this->em->flush();
            $translationSession->addCreatedTargetPhraseAssignment($translation->getId());
            return $translation;
        } else {
            throw new \Error('could not find source with id ' . $sourceId);
        }
    }

    public function flagSource(TranslationSession $translationSession, $sourceId) {
        $sourcePhrase = $this->sourcePhraseRepository->find($sourceId);

        if ($sourcePhrase !== null) {
            $flag = new SourcePhraseFlag();
            $flag->setSourcePhrase($sourcePhrase);
            if ($translationSession->getUserId()) {
                $flag->setUser($this->userRepository->find($translationSession->getUserId()));
            }
            $this->em->persist($flag);
            $this->em->flush();
        } else {
            throw new \Error('could not find source with id ' . $sourceId);
        }
    }

    public function getNextTarget(TranslationSession $translationSession):?TargetPhrase
    {

        $next = $this->targetPhraseRepository->getNextAvailable($translationSession->skipTargetIds());

        if ($next === null) {

            return null;
        } else {
            // store?
//            $translationSession->addTargetPhraseAssignment($next->getId());
            return $next;
        }
    }

    public function skipAndNewValidateAssignment(TranslationSession $translationSession, $targetId):?TargetPhrase {

        $translationSession->addSkipTargetPhraseAssignment($targetId);
        // todo implement skip in session

        return $this->targetPhraseRepository->getNextAvailable($translationSession->skipTargetIds());
    }

    public function validateTarget(TranslationSession $translationSession, $targetId, $score):?TargetPhrase {

        $translationSession->addValidatedTargetPhraseAssignment($targetId);

        $target = $this->targetPhraseRepository->find($targetId);
        $rating = new Rating();
//        $rating->setTarget($target);
        $target->addRating($rating);
        $rating->setTimestamp($this->timeService->currentDateTime());
        $rating->setValue($score);
        if ($translationSession->getUserId()) {
            $rating->setUser($this->userRepository->find($translationSession->getUserId()));
        }
        $this->em->persist($rating);
        $this->em->flush();
        $translationSession->addRatingId($rating->getId());

        /*
Step 1. A pending sentence is reviewed twice
2 positive >> done positive
 (+ +) >> total of thumbs up /2 (rounded down) >> score: 1, 2 or 3
2 negative >> done negative
 (- -) >> total of thumbs down /2 >> score: -1
1 positive & 1 negatie >> Step 2 • (+-)

Step 2. A pending sentence is reviewed twice
2 positive >> done positive (+-/++)>>totalofthumbsup/3(roundeddown)>>score:1,2or3
2 negative (- -) >> done negative (+-/--)>>totalofthumbsdown/3>>score:-1
1 positive & 1 negatie >> done positive if total of thumbs up in both steps is ≧4 (+-/+-)>>totalofthumbsup/3(roundeddown)>>score:1or2
1 positive & 1 negatie >> done neutral if total of thumbs up in both steps is <4 (+-/+-)>>score:0
Keep for optional review later

         */
        //
        // check on target if this should change the validation status
        //
        //
        $ratings = $target->getRatings()->toArray();
        $numRatings = count($ratings);
        if ($numRatings > 1) {
            $positives = 0;
            $negatives = 0;
            $totalThumbsUpScore = 0;
            foreach ($target->getRatings() as $r) {
                if ($r->getValue() > 0) {
                    $positives += 1;
                    $totalThumbsUpScore += $r->getValue();
                } else {
                    $negatives += 1;
                }
            }

            if ($numRatings === 2) {
                if ($positives >= 2) {
                    $target->setValidationState(TargetPhrase::VALIDATION_STATE_POSITIVE);
                    $target->setValidationScore(intdiv($totalThumbsUpScore, 2));
                } else if ($negatives >= 2) {
                    $target->setValidationState(TargetPhrase::VALIDATION_STATE_NEGATIVE);
                    $target->setValidationScore(-1);
                }
                // otherwise no action -> keep open

            } else if ($numRatings === 4) {

                if ($positives >= 3) {
                    $target->setValidationState(TargetPhrase::VALIDATION_STATE_POSITIVE);
                    $target->setValidationScore(intdiv($totalThumbsUpScore, $positives));
                } else if ($negatives >= 3) {
                    $target->setValidationState(TargetPhrase::VALIDATION_STATE_NEGATIVE);
                    $target->setValidationScore(-1);
                } else {
                    /*
                     * 1 positive & 1 negatie >> done positive if total of thumbs up in both steps is ≧4 (+-/+-)>>totalofthumbsup/3(roundeddown)>>score:1or2
                     * 1 positive & 1 negatie >> done neutral if total of thumbs up in both steps is <4 (+-/+-)>>score:0
                     */
                    if ($totalThumbsUpScore >= 4) {
                        $target->setValidationState(TargetPhrase::VALIDATION_STATE_POSITIVE);
                        $target->setValidationScore(intdiv($totalThumbsUpScore, $positives));
                    } else {
                        $target->setValidationState(TargetPhrase::VALIDATION_STATE_INCONCLUSIVE);
                        $target->setValidationScore(0);
                    }
                }

            } else if ($numRatings > 4) {
                $target->setValidationState(TargetPhrase::VALIDATION_STATE_INCONCLUSIVE);
                $target->setValidationScore(0);
            }
        }
        $this->em->persist($target);
        $this->em->flush();

        return $this->getNextTarget($translationSession);
    }

    public function flagTarget(TranslationSession $translationSession, $targetId)
    {
        $target = $this->targetPhraseRepository->find($targetId);
        $flag = new TargetPhraseFlag();
        $flag->setTargetPhrase($target);
        $flag->setTimestamp($this->timeService->currentDateTime());
        if ($translationSession->getUserId()) {
            $flag->setUser($this->userRepository->find($translationSession->getUserId()));
        }
        $this->em->persist($flag);
        $this->em->flush();
        // store id
        $translationSession->addTargetFlagId($flag->getId());
    }
}